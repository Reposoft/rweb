/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.util.LinkedList;
import java.util.List;

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
import org.tigris.subversion.svnant.SvnCommand;
import org.tigris.subversion.svnant.Update;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
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

/**
 * Uses subclipse {@link http://subclipse.tigris.org/svnant.html SvnAnt} to implement the subversion operations
 *
 * WRite operations are done using SVnAnt, but many read operations use the svnClientAdapter API directly.
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
	 * 
	 * @param clientProvider
	 * @param settings 
	 */
	public ReposWorkingCopySvnAnt(ClientProvider clientProvider, CheckoutSettings settings) {
		client = clientProvider.getSvnClient(settings.getLogin());
		conflictNotifyListener = new ConflictNotifyListener();
		this.addNotifyListener(conflictNotifyListener);
		this.settings = settings;
        if (settings.getWorkingCopyDirectory().list().length > 0) {
        	logger.debug("There is a working copy in {}, need to verify", settings.getWorkingCopyDirectory().getAbsolutePath());
        	validateWorkingCopyMatchesRepositoryUrl(settings.getWorkingCopyDirectory(), settings.getCheckoutUrl());
        }
	}
	
	/**
	 * Allows callback after operations.
	 * @param notifyListener A callback implementation.
	 */
	public void addNotifyListener(NotifyListener notifyListener) {
		logger.debug("Adding notify listener {}", notifyListener.getClass().getSimpleName());
		client.addNotifyListener(notifyListener);
	}

	public void checkout() {
        Checkout co = new Checkout();
        co.setDestpath(settings.getWorkingCopyDirectory());
        co.setUrl(settings.getCheckoutUrl().getUrl());
        logger.info("Checking out using command {}", co);
        execute(co);
	}    

	public void update() throws ConflictException {
		Update update = new Update();
        update.setDir(settings.getWorkingCopyDirectory());
        execute(update);
        Conflict.reportConflicts();
	}
	
	public void update(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#update not implemented yet");
		}
		
	}
	
    public void commit(String commitMessage) throws ConflictException {
    	Commit commit = new Commit();
        commit.setDir(settings.getWorkingCopyDirectory());
        commit.setMessage(commitMessage);
        execute(commit);
        Conflict.reportConflicts();
    }
    
	/**
	 * No logic, just an update and a commit
	 */
	public void synchronize(String commitMessage) throws ConflictException {
		this.update();
		this.commit(commitMessage);
	}

	public boolean hasLocalChanges() {
		return hasLocalChanges(settings.getWorkingCopyDirectory());
	}
	
	public boolean hasLocalChanges(File path) {
        ISVNStatus[] statuses = null;
        try {
            statuses = client.getStatus(path, true, true); //descend, all
        } catch (SVNClientException e) {
            throw new RuntimeException("Handling for SVNClientException not implemented", e);
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
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#add not implemented yet");
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
        execute(add);
    }

	public void delete(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#delete not implemented yet");
		}
		
	}

	public void lock(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#lock not implemented yet");
		}
		
	}

	public void move(File from, File to) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#move not implemented yet");
		}
		
	}

	public void revert(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#revert not implemented yet");
		}
		
	}	

	public void markConflictResolved(ConflictInformation conflictInformation) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#markConflictResolved not implemented yet");
		}
		
	}

    private void execute(SvnCommand command) {
    	if (command.getProject() == null) {
    		command.setProject(ANTPROJECT); // dummy, might be needed for some operations
    	}
        try {
        	command.execute(client);
        } catch (BuildException be) {
        	// this kind of errors should be handled by the notify listener
        	logger.error("Svn client error '" + be.getMessage() + "' caused by: " + be.getCause().getMessage(), be);
        }
    }
	
    /**
     * @param fileOrDirStatus from the wokring copy
     * @return true if there is something to commit according to the status.
     * 	unversion files are counted as modifications.
     */
	boolean hasLocalChanges(ISVNStatus fileOrDirStatus) {
		SVNStatusKind textStatus = fileOrDirStatus.getTextStatus();
		SVNStatusKind propStatus = fileOrDirStatus.getPropStatus();
		if (SVNStatusKind.MODIFIED.equals(textStatus)) {
		    return true; // could also check for conflicts
		}
		if (SVNStatusKind.MODIFIED.equals(propStatus)) {
		    return true;
		}
		if (SVNStatusKind.UNVERSIONED.equals(textStatus)) {
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

	public boolean isVersioned(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#isVersioned not implemented yet");
		}
		return false;
	}
	
	ISVNClientAdapter getClient() {
		return client;
	}
	
	NotifyListener getConflictNotifyListener() {
		return conflictNotifyListener;
	}
	
	/**
	 * Need to be able to throw Conflict as runtime exception in notify listener
	 * @todo make an instance variable with the conflict stack in enclosing type instead
	 */
	static class Conflict extends RuntimeException {
		private static final long serialVersionUID = 1L;
		// keep track of last conflict in a non threadsafe way
		private static List conflicts = new LinkedList();
		private static void push(Conflict newreport) {
			Conflict.conflicts.add(newreport);
		}
		/**
		 * To be called after each operation that could possibly cause a conflict.
		 * @throws ConflictException if there was a conflict at the last operation
		 */
		static void reportConflicts() throws ConflictException {
			if (Conflict.conflicts.isEmpty()) {
				return;
			}
			ConflictInformation[] c = new ConflictInformation[conflicts.size()];
			for (int i = 0; i < c.length; i++) {
				c[i] = conflicthandler
			}
			Conflict.conflicts.clear();
			throw new ConflictException(new ConflictInformation[0]);
		}
		
		// the instcances
		String filename;
		boolean previouslyReported = false; // true if this is an old conflict that was never resolved
		public Conflict(String filename) {
			this.filename = filename;
			push(this);
		}
		public Conflict(String filename, boolean previouslyReported) {
			this(filename);
			this.previouslyReported = previouslyReported;
		}
		public String getMessage() {
			return "Conflict at " + filename + ". This error should not be seen outside client.";
		}
		String getFilename() {
			return filename;
		}
		boolean isPreviouslyReported() {
			return previouslyReported;
		}
	}
	
	/**
	 * Mandatory notify lsitener that provides logging and conflict detection.
	 * 
	 * Don't throw exceptions from this class. They'll only be silently caught in the JavaSVN lib.
	 */
	private class ConflictNotifyListener implements NotifyListener {
		private int currentCommand;
		private int counter = 0;
		
		public void setCommand(int command) {
			counter++;
			this.currentCommand = command;			
		}

		private String getCurrentCommand() {
			return "" + counter + ":" + currentCommand;
		}
		
		public void logCommandLine(String commandLine) {
			logger.debug("svn command line for {}: {}", getCurrentCommand(), commandLine);
		}

		public void logCompleted(String message) {
			logger.info("svn completed command {}: {}", getCurrentCommand(), message);
		}

		// conflict is reported as "C  C:/myfile.txt"
		public void logError(String message) {
			logger.error("svn error in command {}: {}", getCurrentCommand(), message);
			if (message.matches("^*C\\s+.+$")) {
				logger.warn("Conflict detected: {}", message);
				//svn client lib has to finis its work// throw new ConflictStack(message);
				new Conflict(message);
			}
		}

		public void logMessage(String message) {
			logger.debug("svn message from command {}: {}", getCurrentCommand(), message);
		}

		public void logRevision(long revision, String path) {
			logger.info("svn path {} now at revision {}", path, revision);
		}

		public void onNotify(File path, SVNNodeKind kind) {
			logger.info("svn command {} running on path {} ({})", new Object[]{getCurrentCommand(), path, kind});
		}
	}

	/**
	 * Inject the logic for reporting ConflictInformation
	 * Currently this is done by {@link ReposWorkingCopyFactory} but it should probably be done by the application.
	 * @param conflictHandler istance
	 */
	public void setConflictHandler(ConflictHandler conflictHandler) {
		this.conflictHandler = conflictHandler;
	}
	
}
