/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.util.LinkedList;
import java.util.List;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.apache.tools.ant.BuildException;
import org.apache.tools.ant.DirectoryScanner;
import org.apache.tools.ant.FileScanner;
import org.apache.tools.ant.Project;
import org.apache.tools.ant.types.FileSet;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.tigris.subversion.svnant.Add;
import org.tigris.subversion.svnant.Checkout;
import org.tigris.subversion.svnant.Commit;
import org.tigris.subversion.svnant.Delete;
import org.tigris.subversion.svnant.Move;
import org.tigris.subversion.svnant.Revert;
import org.tigris.subversion.svnant.SvnCommand;
import org.tigris.subversion.svnant.Update;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.ISVNNotifyListener;
import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.SVNNodeKind;
import org.tigris.subversion.svnclientadapter.SVNStatusKind;
import org.tigris.subversion.svnclientadapter.SVNUrl;

import se.repos.svn.ClientProvider;
import se.repos.svn.RepositoryUrl;
import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.NotifyListener;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.ReposWorkingCopyFactory;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.VersionedProperties;
import se.repos.svn.checkout.WorkingCopyAccessException;
import se.repos.svn.config.ClientConfiguration;

/**
 * Uses subclipse {@link http://subclipse.tigris.org/svnant.html SvnAnt} to implement the subversion operations
 *
 * This implementation is not at all forgiving. There is usually only one correct way of doing things here.
 * For example doing {@link #delete(File)} on a missing file causes an IllegalArgumentException.
 * Where it is obvious that a {@see File} must exist, for example in {@see {@link #lock(File)}, 
 * NullPointerExceptions might be thrown on invlid input.
 * Use the managed clients to get supporting logic.
 *
 * This class uses the {@link http://www.slf4j.org/ slf4j} logging API.
 * See the slf4j docs on how to customize output.
 * 
 * This is a stateful implementation. The instance has its own ISVNClientAdapter,
 * which has a username and password set using {@link #setUserCredentials(UserCredentials)}.
 * Each instance of this class should be used in one thread only.
 * It seems that all SVN client libraries are non-threadsafe.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ReposWorkingCopySvnAnt implements ReposWorkingCopy {
	
	final Logger logger = LoggerFactory.getLogger(this.getClass());	
	
	ISVNClientAdapter client;
	
	CheckoutSettings settings;
	
	ConflictNotifyListener conflictNotifyListener;
	
	// corrently it is not verified that the application sets this
	protected ConflictHandler conflictHandler = null;
	
	//used for all Ant calls that need a Project instance
    private final Project ANTPROJECT = new Project();
	
    /**
     * Default constructor for use in testing.
     * Requires all dependencies to be set with setters.
     */
    ReposWorkingCopySvnAnt() {
    	// required setup
    	conflictNotifyListener = new ConflictNotifyListener();
	}
    
    /**
     * @param client the initialized svn client, must be set before any svn operation
     */
	void setClientAdapter(ISVNClientAdapter client) {
		this.client = client;
		this.addNotifyListener(conflictNotifyListener);
	}

	/**
	 * @param settings the working copy settings, must be set before any svn operation
	 */
	void setCheckoutSettings(CheckoutSettings settings) {
		this.settings = settings;
	}
	
	/**
	 * Inject the logic for reporting ConflictInformation
	 * Currently this is done by {@link ReposWorkingCopyFactory} but it should probably be done by the application.
	 * @param conflictHandler istance
	 */
	void setConflictHandler(ConflictHandler conflictHandler) {
		this.conflictHandler = conflictHandler;
	}
	
	/**
	 * @return for testing
	 */
	ISVNClientAdapter getClientAdapter() {
		return client;
	}

	/**
	 * @return for testing
	 */
	NotifyListener getConflictNotifyListener() {
		return conflictNotifyListener;
	}
	
	/**
	 * Verify set up
	 */
	void afterPropertiesSet() {
		if (settings.getWorkingCopyDirectory().list().length > 0) {
        	logger.debug("There is a working copy in {}", settings.getWorkingCopyDirectory().getAbsolutePath());
        	validateWorkingCopyMatchesRepositoryUrl(settings.getWorkingCopyDirectory(), settings.getCheckoutUrl());
        }
	}
    
	/**
	 * 
	 * @param clientProvider
	 * @param settings 
	 */
	public ReposWorkingCopySvnAnt(ClientProvider clientProvider, CheckoutSettings settings, ConflictHandler conflictHandler) {
		// set up
		this();
		setClientAdapter(clientProvider.getSvnClient(settings.getLogin()));
		setCheckoutSettings(settings);
		setConflictHandler(conflictHandler);
		afterPropertiesSet();
	}
	
	/**
	 * Allows callback after operations.
	 * @param notifyListener A callback implementation.
	 */
	public void addNotifyListener(NotifyListener notifyListener) {
		logger.debug("Adding notify listener {}", notifyListener.getClass().getSimpleName());
		client.addNotifyListener(notifyListener);
	}
	
	/**
	 * To be called after every operation that could cause a conflict
	 * @throws ConflictException
	 */
	void reportConflicts() throws ConflictException {
		conflictNotifyListener.reportConflicts();
	}

	public void checkout() throws RepositoryAccessException {
        Checkout co = new Checkout();
        co.setDestpath(settings.getWorkingCopyDirectory());
        co.setUrl(settings.getCheckoutUrl().getUrl());
        logger.info("Checking out using command {}", co);
        try {
			execute(co);
		} catch (SVNClientException e) {
			throw new RepositoryAccessException(e);
		}
	}    

	public void update() throws ConflictException, RepositoryAccessException {
		Update update = new Update();
        update.setDir(settings.getWorkingCopyDirectory());
        try {
			execute(update);
		} catch (SVNClientException e) {
			throw new RepositoryAccessException(e);
		}
        reportConflicts();
	}
	
	public void update(File path) throws RepositoryAccessException, ConflictException {
		Update update = new Update();
		if (path.isDirectory()) {
			update.setDir(path);
		} else {
			update.setFile(path);
		}
		try {
			execute(update);
		} catch (SVNClientException e) {
			throw new RepositoryAccessException(e);
		}
		reportConflicts();
	}
	
    public void commit(String commitMessage) throws ConflictException, RepositoryAccessException {
    	logger.info("Committing working copy {} with message: {}", settings.getWorkingCopyDirectory().getAbsolutePath(), commitMessage);
    	Commit commit = new Commit();
        commit.setDir(settings.getWorkingCopyDirectory());
        commit.setMessage(commitMessage);
        try {
			execute(commit);
		} catch (SVNClientException e) {
			throw new RepositoryAccessException(e);
		}
        reportConflicts();
    }
    
	/**
	 * No logic, just an update and a commit
	 */
	public void synchronize(String commitMessage) throws ConflictException, RepositoryAccessException {
		this.update();
		this.commit(commitMessage);
	}

	public boolean isVersioned(File path) throws WorkingCopyAccessException {
		try {
			ISVNStatus status = getSingleStatus(path);
			return (status.getTextStatus() != SVNStatusKind.UNVERSIONED);
		} catch (WorkingCopyAccessException e) {
			// throws client exception if the parent path is not versioned
			try {
				if (!isVersioned(path.getParentFile())) return false;
			} catch (Throwable t) {}
			throw e;
		}
	}

	private ISVNStatus getSingleStatus(File path) {
		try {
			return client.getSingleStatus(path);
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
	}
	
	public boolean hasLocalChanges() {
		return hasLocalChanges(settings.getWorkingCopyDirectory());
	}
	
	public boolean hasLocalChanges(File path) {
        ISVNStatus[] statuses = null;
        try {
            statuses = client.getStatus(path, true, true); //descend, all
        } catch (SVNClientException e) {
            throw new WorkingCopyAccessException(e);
        }
        // will exit and return true when it finds a modified file/dir
        for (int i = 0; i<statuses.length; i++) {
            ISVNStatus st = statuses[i];
            logger.debug(st.getPath() + ": TextStatus=" + st.getTextStatus() + ", PropStatus=" + st.getPropStatus());
            if (hasLocalChanges(st)) {
            	return true;
            }
        }
        return false;
	}
	
	public void add(File path) {
		if (!path.exists()) throw new IllegalArgumentException("Can not add the file '" + path + "' because it does not exist");
		Add add = new Add();
		if (path.isDirectory()) {
			add.setDir(path);
		} else {
			add.setFile(path);
		}
		try {
			execute(add);
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
	}
	
    public void addAll() {
        FileScanner directoryScanner = new DirectoryScanner();
        directoryScanner.setBasedir(settings.getWorkingCopyDirectory());
        FileSet fileSet = new FileSet();
        fileSet.setupDirectoryScanner(directoryScanner, ANTPROJECT);
        fileSet.setDir(settings.getWorkingCopyDirectory());
        Add add = new Add();
        add.addFileset(fileSet);
        try {
			execute(add);
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
    }

    /**
     * It is adviced that applicaiton first checks if the file has local modifications,
     * because then it can't be deleted.
     */
	public void delete(File path) throws WorkingCopyAccessException {
		Delete delete = new Delete();
		if (path.isDirectory()) {
			delete.setDir(path);
		} else {
			delete.setFile(path);
		}
		try {
			execute(delete);
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
	}

	public void lock(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#lock not implemented yet");
		}
		
	}

	public void move(File from, File to) {
		Move move = new Move();
		move.setSrcPath(from);
		move.setDestPath(to);
		try {
			execute(move);
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
	}

	/**
	 * Always does recursive revert on directories, as specified by interface.
	 */
	public void revert(File path) {
		Revert revert = new Revert();
		if (path.isDirectory()) {
			revert.setDir(path);
			revert.setRecurse(true);
		} else {
			revert.setFile(path);
		}
		try {
			execute(revert);
		} catch (SVNClientException e) {
			// revert is a local operation
			throw new WorkingCopyAccessException(e);
		}
	}	

	public void markConflictResolved(ConflictInformation conflictInformation) throws WorkingCopyAccessException {
		try {
			client.resolved(conflictInformation.getTargetPath());
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
		conflictHandler.afterConflictResolved(conflictInformation);
	}

	/**
	 * @param command SvnAnt command
	 * @throws SVNClientException to force the caller to categorize the error
	 */
    void execute(SvnCommand command) throws SVNClientException {
    	if (command.getProject() == null) {
    		command.setProject(ANTPROJECT); // dummy, might be needed for some operations
    	}
    	try {
    		command.execute(client);
    	} catch (BuildException e) {
    		if (e.getCause() instanceof SVNClientException) {
    			throw (SVNClientException) e.getCause();
    		} else {
    			throw new RuntimeException("Svn client error '" + e.getMessage() + "' caused by: " + e.getCause().getMessage(), e);
    		}
    	}
    }
	
    /**
     * @param fileOrDirStatus from the wokring copy
     * @return true if there is something to commit according to the status.
     */
	boolean hasLocalChanges(ISVNStatus fileOrDirStatus) {
		// currently this is not implemented with a systematic approach,
		// it's based on the test cas
		SVNStatusKind textStatus = fileOrDirStatus.getTextStatus();
		SVNStatusKind propStatus = fileOrDirStatus.getPropStatus();
		if (SVNStatusKind.UNVERSIONED.equals(textStatus)) {
		    return false; // could also check for conflicts
		}
		if (SVNStatusKind.MODIFIED.equals(textStatus)) {
		    return true; // could also check for conflicts
		}
		if (SVNStatusKind.DELETED.equals(textStatus)) {
			return true;
		}
		if (SVNStatusKind.MODIFIED.equals(propStatus)) {
		    return true;
		}
		if (SVNStatusKind.ADDED.equals(textStatus)) {
		    return true; // could also check for conflicts
		}
		return false;
	}	
    
	private void validateWorkingCopyMatchesRepositoryUrl(File workingCopyDirectory, RepositoryUrl checkoutUrl) {
		SVNUrl actual = getActualRepositoryUrl(workingCopyDirectory);
		if (!checkoutUrl.equals(actual)) {
			throw new IllegalArgumentException("The existing check out URL is '" + checkoutUrl
					+ "' but the working copy URL at '" + workingCopyDirectory.getPath() + "' is: " + checkoutUrl);
		}
		logger.info("The repository URL of {} matches the specified: {}", workingCopyDirectory.getPath(), checkoutUrl);
	}
	
	public SVNUrl getActualRepositoryUrl(File workingCopyDirectory) {
		try {
			ISVNStatus status = client.getSingleStatus(workingCopyDirectory);
			return status.getUrl();
		} catch (SVNClientException e) {
			throw new RuntimeException("SVNClientException handling missing", e);
		}
	}
	
	/**
	 * Mandatory notify lsitener that provides logging and conflict detection.
	 * 
	 * Don't throw exceptions from the ISVNNotifyListener methods. They'll only be silently caught in the JavaSVN lib.
	 */
	private class ConflictNotifyListener implements NotifyListener {
		private String currentCommand;
		private int counter = 0;
		
		// recently encountered conflicts
		private List conflictFileList = new LinkedList();
		
		/**
		 * To be called after each operation that could possibly cause a conflict.
		 * @throws ConflictException if there was a conflict at the last operation
		 */
		private void reportConflicts() throws ConflictException {
			if (conflictFileList.isEmpty()) {
				return;
			}
			ConflictInformation[] c = new ConflictInformation[conflictFileList.size()];
			for (int i = 0; i < c.length; i++) {
				c[i] = conflictHandler.handleConflictingFile((File)conflictFileList.get(i));
			}
			conflictFileList.clear();
			throw new ConflictException(c);
		}
		
		public void setCommand(int command) {
			counter++;
			currentCommand = getCommandName(command);		
		}

		private String getCurrentCommand() {
			return "" + counter + ":" + currentCommand;
		}
		
		public void logCommandLine(String commandLine) {
			logger.debug("svn command line for {}: {}", getCurrentCommand(), commandLine);
		}

		public void logCompleted(String message) {
			logger.debug("svn completed command {}: {}", getCurrentCommand(), message);
		}

		// conflict is reported as "C  C:/myfile.txt"
		public void logError(String message) {
			Pattern conflictPattern = Pattern.compile("^\\s*C\\s+(.+)\\s*$");
			Matcher conflictMatcher = conflictPattern.matcher(message);
			if (conflictMatcher.matches()) {
				logger.warn("Conflict detected: {}", message);
				String filename = conflictMatcher.group(1);
				conflictFileList.add(new File(filename));
			} else {
				logger.error("Subversion error in command {}: {}", getCurrentCommand(), message);
			}
		}

		public void logMessage(String message) {
			logger.debug("svn message from command {}: {}", getCurrentCommand(), message);
		}

		public void logRevision(long revision, String path) {
			logger.info("Now at revision {} for path {}", Long.toString(revision), path);
		}

		public void onNotify(File path, SVNNodeKind kind) {
			logger.debug("svn command {} running on path {} ({})", new Object[]{getCurrentCommand(), path, kind});
		}
		
		private String getCommandName(int command) {
			switch (command) {
			case ISVNNotifyListener.Command.UPDATE: return "update";
			case ISVNNotifyListener.Command.ADD: return "add";
			case ISVNNotifyListener.Command.ANNOTATE: return "annotate";
			case ISVNNotifyListener.Command.CAT: return "cat";
			case ISVNNotifyListener.Command.CHECKOUT: return "checkout";
			case ISVNNotifyListener.Command.CLEANUP: return "cleanup";
			case ISVNNotifyListener.Command.COMMIT: return "commit";
			case ISVNNotifyListener.Command.COPY: return "copy";
			case ISVNNotifyListener.Command.CREATE_REPOSITORY: return "create_repository";
			case ISVNNotifyListener.Command.DIFF: return "diff";
			case ISVNNotifyListener.Command.EXPORT: return "export";
			case ISVNNotifyListener.Command.IMPORT: return "import";
			case ISVNNotifyListener.Command.INFO: return "info";
			case ISVNNotifyListener.Command.LOCK: return "lock";
			case ISVNNotifyListener.Command.LOG: return "log";
			case ISVNNotifyListener.Command.LS: return "ls";
			case ISVNNotifyListener.Command.MERGE: return "merge";
			case ISVNNotifyListener.Command.MKDIR: return "mkdir";
			case ISVNNotifyListener.Command.MOVE: return "move";
			case ISVNNotifyListener.Command.PROPDEL: return "propdel";
			case ISVNNotifyListener.Command.PROPGET: return "propget";
			case ISVNNotifyListener.Command.PROPLIST: return "proplist";
			case ISVNNotifyListener.Command.PROPSET: return "propset";
			case ISVNNotifyListener.Command.RELOCATE: return "relocate";
			case ISVNNotifyListener.Command.REMOVE: return "remove";
			case ISVNNotifyListener.Command.RESOLVED: return "resolved";
			case ISVNNotifyListener.Command.REVERT: return "revert";
			case ISVNNotifyListener.Command.STATUS: return "status";
			case ISVNNotifyListener.Command.SWITCH: return "switch";
			case ISVNNotifyListener.Command.UNDEFINED: return "undefined";
			case ISVNNotifyListener.Command.UNLOCK: return "unlock";
			}
			return "(unknown type " + command + ")";
		}
	}

	public void unlock(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#unlock not implemented yet");
		}
		
	}

	public void revert() {
		this.revert(settings.getWorkingCopyDirectory());
	}

	public boolean isMetadataFolder(File path) {
		return client.isAdminDirectory(path.getName());
	}

	public boolean isIgnore(File path) {
		if (isVersioned(path)) return false; // svn status is of no help to see if a version file matches ignore patterns
		
		ISVNStatus status = getSingleStatus(path);
		return (status.getTextStatus() != SVNStatusKind.IGNORED);
	}

	public VersionedProperties getProperties(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#getProperties not implemented yet");
		}
		return null;
	}

	public ClientConfiguration getClientSettings() {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#getClientSettings not implemented yet");
		}
		return null;
	}
	
}
