/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.util.LinkedList;
import java.util.List;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.ISVNNotifyListener;
import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.SVNNodeKind;
import org.tigris.subversion.svnclientadapter.SVNRevision;
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
import se.repos.svn.checkout.VersionedFileProperties;
import se.repos.svn.checkout.VersionedFolderProperties;
import se.repos.svn.checkout.VersionedProperties;
import se.repos.svn.checkout.WorkingCopyAccessException;
import se.repos.svn.config.ClientConfiguration;
import se.repos.svn.config.ConfigurationStateException;

/**
 * Uses subclipse {@link subclipse.tigris.org/svnClientAdapter.html svnClientAdapter} to implement the subversion operations
 *
 * This is the default {@link ReposWorkingCopy} implementation (please refer to that for usage documentation).
 * It would be possible to write implementations that use client libs like javahl directly.
 *
 * This implementation does not try to anticipate or understand what the user does.
 * Simply runs the SVN operations to mimic the command line client.
 * The 'principle of least surprise' is important, leaving business logic to the level above.
 * Use the managed clients to get supporting logic.
 * 
 * No methods accept null arguments, but there are no explicit checks for that so invalid input causes NullPointerExceptions.
 *
 * This class uses the {@link http://www.slf4j.org/ slf4j} logging API.
 * See the slf4j docs on how to customize output.
 * Does 'info' logging of online operations,
 * and 'debug' logging of offline operations.
 * 
 * This is a stateful implementation. The instance has its own ISVNClientAdapter,
 * which has a username and password set using {@link #setUserCredentials(UserCredentials)}.
 * Each instance of this class should be used in one thread only.
 * It seems that all SVN client libraries are non-threadsafe.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ReposWorkingCopySvn implements ReposWorkingCopy {
	
	final Logger logger = LoggerFactory.getLogger(this.getClass());	
	
	ISVNClientAdapter client;
	
	ClientConfiguration clientConfiguration;
	
	CheckoutSettings settings;
	
	ConflictNotifyListener conflictNotifyListener;
	
	// corrently it is not verified that the application sets this
	protected ConflictHandler conflictHandler = null;
	
	/**
	 * Complete initialization of the client class for one working copy with one user.
	 * 
	 * @param clientProvider Used to get the svnClientAdapter and the {@link ClientConfiguration}
	 * @param settings for the work session
	 * @param conflictHandler pluggable callback behaviour when conflicts are detected
	 * @see #afterPropertiesSet()
	 */
	public ReposWorkingCopySvn(ClientProvider clientProvider, CheckoutSettings settings, ConflictHandler conflictHandler) {
		// set up
		this();
		setClientAdapter(clientProvider.getSvnClient(settings.getLogin()));
		try {
			setClientConfiguration(clientProvider.getRuntimeConfiguration());
		} catch (ConfigurationStateException e) {
			logger.error("Could not read runtime configuration area settings. Client configuration is unknown.");
			// until ClientConfiguration is well tested, proceed // throw new RuntimeException("ConfigurationStateException thrown, not handled", e);
		}
		setCheckoutSettings(settings);
		setConflictHandler(conflictHandler);
		afterPropertiesSet();
	}
	
    /**
     * Default constructor for use in testing.
     * Requires all dependencies to be set with setters.
     */
    ReposWorkingCopySvn() {
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
	 * @param svnConfiguration runtime configuration area contents customizing local subversion clients' behaviur
	 */
	void setClientConfiguration(ClientConfiguration svnConfiguration) {
		this.clientConfiguration = svnConfiguration;
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
	 * Should be called when the client is initialized and all properties set.
	 * Verifies settings.
	 */
	private void afterPropertiesSet() throws IllegalStateException {
		if (settings == null) throw new IllegalStateException("CheckoutSettings must be set");
		if (client == null) throw new IllegalStateException("SvnClientAdapter client must be provided");
		if (clientConfiguration == null) throw new IllegalStateException("ClientConfiguration must be set");
		if (conflictHandler == null) throw new IllegalStateException("conflictHandler must be set");
		
		// check current contents of the working copy folder
        validateWorkingCopyContentsAtClientCreation(settings.getWorkingCopyFolder(), settings.getCheckoutUrl());
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
		File path = settings.getWorkingCopyFolder();
		if (path.list().length > 0) throw new IllegalStateException("Can not check out to " + path + " because the folder is not empty.");
        logger.info("Checking out {} HEAD recursively to {}", settings.getCheckoutUrl(), settings.getWorkingCopyFolder());
        try {
			client.checkout(
					settings.getCheckoutUrl().getUrl(),
					path, 
					SVNRevision.HEAD,
					true);
		} catch (SVNClientException e) {
			RepositoryAccessException.handle(e);
		}
	}    

	public void update() throws ConflictException, RepositoryAccessException {
		update(settings.getWorkingCopyFolder());
	}
	
	public void update(File path) throws RepositoryAccessException, ConflictException {
		logger.info("Doing Update to HEAD in folder {}", path);
        try {
			client.update(path, SVNRevision.HEAD, true);
		} catch (SVNClientException e) {
			RepositoryAccessException.handle(e);
		}
        reportConflicts();
	}
	
    public void commit(String commitMessage) throws ConflictException, RepositoryAccessException {
    	logger.info("Committing working copy {} with message: {}", settings.getWorkingCopyFolder().getAbsolutePath(), commitMessage);
        try {
			client.commit(new File[]{settings.getWorkingCopyFolder()}, commitMessage, true);
		} catch (SVNClientException e) {
			RepositoryAccessException.handle(e);
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
			return (status.getTextStatus() != SVNStatusKind.UNVERSIONED 
					&& status.getTextStatus() != SVNStatusKind.IGNORED);
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
		return hasLocalChanges(settings.getWorkingCopyFolder());
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
		try {
			if (path.isDirectory()) {
				logger.debug("Adding a folder but not its contents: {}", path);
				client.addDirectory(path, false, true);
			} else {
				logger.debug("Adding file: {}", path);
				client.addFile(path);
			}
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
	}
	
    public void addNew() {
        addNew(settings.getWorkingCopyFolder());
    }
    
    public void addNew(File path) {
    	logger.info("Adding all new folders and files (except ignored) in path {}", settings.getWorkingCopyFolder());
        try {
			client.addDirectory(settings.getWorkingCopyFolder(), true, false);
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
    }

    /**
     * It is adviced that applicaiton first checks if the file has local modifications,
     * because then it can't be deleted.
     */
	public void delete(File path) throws WorkingCopyAccessException {
		logger.debug("Deleting file or folder {}, unless it has local changes", path);
		try {
			client.remove(new File[]{path}, false);
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
		logger.debug("Deleting {} to {}, unless it has local changes", from, to);
		try {
			client.move(from, to, false);
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
	}

	/**
	 * Always does recursive revert on directories, as specified by interface.
	 */
	public void revert(File path) {
		try {
			if (path.isDirectory()) {
				logger.info("Reverting folder {} recursively. If it contains deleted folders, update might be needed.", path);
				client.revert(path, true);
			} else {
				logger.debug("Reverting file {}", path);
				client.revert(path, true);
			}
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
     * @param fileOrDirStatus from the wokring copy
     * @return true if there is something to commit according to the status.
     */
	boolean hasLocalChanges(ISVNStatus fileOrDirStatus) {
		// currently this is not implemented with a systematic approach,
		// it's based on the test cases
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
    
	/**
	 * This implementation expects wc folder to be empty or contain a working copy that matches the URL.
	 * @param workingCopyFolder the folder where the client should find the working copy
	 * @param checkoutUrl the URL that was provided for repository access
	 * @throws IllegalStateException if the folder is not valid as working copy
	 */
	protected void validateWorkingCopyContentsAtClientCreation(File workingCopyFolder, RepositoryUrl checkoutUrl)
			throws IllegalStateException {
		if (workingCopyFolder.list().length == 0) {
        	return; // folder is empty, which is ok
        }
		logger.debug("Working copy folder '{}' is not empty", settings.getWorkingCopyFolder().getAbsolutePath());
		SVNUrl actual = getActualRepositoryUrl(workingCopyFolder);
		if (actual == null) {
			return; // this is not a working copy
		}
		if (!checkoutUrl.equals(actual)) {
			throw new IllegalStateException("The existing check out URL is '" + checkoutUrl
					+ "' but the working copy URL at '" + workingCopyFolder.getPath() + "' is: " + checkoutUrl);
		}
		logger.info("The repository URL of {} matches the specified: {}", workingCopyFolder.getPath(), checkoutUrl);
	}
	
	public SVNUrl getActualRepositoryUrl(File workingCopyDirectory) {
		try {
			ISVNStatus status = client.getSingleStatus(workingCopyDirectory);
			return status.getUrl();
		} catch (SVNClientException e) {
			throw new RuntimeException("Got a client error when verifying the working copy folder", e);
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
			return currentCommand+counter;
		}
		
		public void logCommandLine(String commandLine) {
			logger.debug("svn command line for {} '{}'", getCurrentCommand(), commandLine);
		}

		public void logCompleted(String message) {
			logger.debug("svn completed command {} '{}'", getCurrentCommand(), message);
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
				logger.error("Subversion error in command {} '{}'", getCurrentCommand(), message);
			}
		}

		public void logMessage(String message) {
			logger.debug("svn message from command {} '{}'", getCurrentCommand(), message);
		}

		public void logRevision(long revision, String path) {
			logger.info("Now at revision {} for path {}", Long.toString(revision), path);
		}

		public void onNotify(File path, SVNNodeKind kind) {
			logger.debug("svn command {} running on {} '{}'", new Object[]{getCurrentCommand(), kind, path});
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
		this.revert(settings.getWorkingCopyFolder());
	}

	public boolean isAdministrativeFolder(File path) {
		return client.isAdminDirectory(path.getName());
	}

	public boolean isIgnore(File path) {
		if (this.settings.getWorkingCopyFolder().equals(path)) return false;
		if (!path.exists()) throw new IllegalArgumentException("Can not check status for non-existing path: " + path);
		if (!isVersioned(path.getParentFile())) throw new IllegalArgumentException("Can not check status. The parent folder is not versioned: " + path.getParentFile());
		if (isVersioned(path)) return false; // svn status is of no help to see if a versioned file matches ignore patterns

		// client.getSingleStatus uses the --no-ignores parameter (for some reason) so it can not be used
		
		ISVNStatus[] result;
		try {
			result = client.getStatus(path, false, true, false); //(File path, boolean descend, boolean getAll, boolean contactServer, boolean ignoreExternals)
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e);
		}
		if (result.length==0) throw new WorkingCopyAccessException("Can not get status for path " + path);
		
		SVNStatusKind status = result[0].getTextStatus();
		
		if (SVNStatusKind.UNVERSIONED.equals(status) && isAdministrativeFolder(path)) return true;
		return SVNStatusKind.IGNORED.equals(status);
	}

	public ClientConfiguration getClientConfiguration() {
		return clientConfiguration;
	}	
	
	public VersionedProperties getProperties(File path) {
		verifyCanAccessProperties(path);
		return new PropertyAccess(path, this.getClientAdapter());
	}

	public VersionedFileProperties getPropertiesForFile(File file) {
		verifyCanAccessProperties(file);
		return new PropertyAccessFile(file, this.getClientAdapter());
	}

	public VersionedFolderProperties getPropertiesForFolder(File folder) {
		verifyCanAccessProperties(folder);
		return new PropertyAccessFolder(folder, this.getClientAdapter());
	}
	
	private void verifyCanAccessProperties(File path) {
		if (!isVersioned(path)) throw new IllegalArgumentException("Can not access properties for the non-versioned path " + path);
	}
	
}
