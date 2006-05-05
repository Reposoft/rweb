package se.repos.svn.checkout.simple;

import java.io.File;
import java.util.ArrayList;
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
import org.tigris.subversion.svnant.Feedback;
import org.tigris.subversion.svnant.SvnCommand;
import org.tigris.subversion.svnant.Update;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.ISVNNotifyListener;
import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.SVNStatusKind;
import org.tigris.subversion.svnclientadapter.SVNUrl;

import se.repos.svn.ClientProvider;
import se.repos.svn.RepositoryUrl;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.file.RejectPathDoesNotExist;
import se.repos.svn.javasvn.TmateSvnClientProvider;
import se.repos.validation.ValidationRule;

/**
 * Basic working copy using the {@link http://subclipse.tigris.org/svnant.html SvnAnt} commands.
 *
 * Designed to make interactions minimal and as clear sa possible for a
 * user who has no experience with source code versioning systems.
 *
 * @author Staffan Olsson
 * @since 2006-apr-16
 * @version $Id$
 * @todo Should all files be write-protected until locked?
 */
public class PersonalWorkingCopy implements ReposWorkingCopy {

	final Logger logger = LoggerFactory.getLogger(this.getClass());
	
	// listeners to register to the client
    private final List notifyListeners = new ArrayList();
    
    // used for all Ant calls that need a Project instance
    private final Project project = new Project();
    
    // the client pool/factory. could be injected
    private ClientProvider clientProvider = new TmateSvnClientProvider();
  
    private static final ValidationRule<CheckoutSettings> CHECKOUT_SETTINGS_VALIDATOR = 
    	new CheckoutSettingsValidator();
    
    /**
     * Working copy configuration.
     */
    private CheckoutSettings settings;
    
    /**
     * Open up an existing working copy.
     * If the folder is empty nothing can be done until {@link #checkout()} is called.
     */
    public PersonalWorkingCopy(CheckoutSettings settings) {
        super();
        logger.info("Initializing working copy with settings: {}", settings);
        CHECKOUT_SETTINGS_VALIDATOR.validate(settings);
        this.settings = settings;
        if (settings.getWorkingCopyDirectory().list().length == 0) {
        	logger.debug("Working copy direcotry {} is empty, need new checkout", settings.getWorkingCopyDirectory().getAbsolutePath());
        	checkoutNewWorkingCopy();
        } else {
        	logger.debug("There is a working copy in {}, need to verify", settings.getWorkingCopyDirectory().getAbsolutePath());
        	validateWorkingCopyMatchesRepositoryUrl(settings.getWorkingCopyDirectory(), settings.getCheckoutUrl());
        }
    }

	/**
     * Checkout all files to the empty working copy directory
     */
    public void checkoutNewWorkingCopy() {
        Checkout co = new Checkout();
        co.setDestpath(getLocalRootDir());
        co.setUrl(getUrl());
        logger.info("Checking out using command {}", co);
        execute(co);
    }

	private void validateWorkingCopyMatchesRepositoryUrl(File workingCopyDirectory, RepositoryUrl checkoutUrl) {
		SVNUrl actual = getActualRepositoryUrl(workingCopyDirectory);
		if (!checkoutUrl.equals(actual)) {
			throw new IllegalArgumentException("The existing choeck out URL is '" + checkoutUrl
					+ "' but the working copy URL at '" + workingCopyDirectory.getPath() + "' is: " + checkoutUrl);
		}
		logger.info("The repository URL of {} matches the specified: {}", workingCopyDirectory.getPath(), checkoutUrl);
	}
	
	public SVNUrl getActualRepositoryUrl(File workingCopyDirectory) {
		ISVNClientAdapter client = getClientAdapter();
		try {
			ISVNStatus status = client.getSingleStatus(workingCopyDirectory);
			return status.getUrl();
		} catch (SVNClientException e) {
			throw new RuntimeException("SVNClientException handling missing", e);
		}
	}

    public boolean hasLocalChanges() {
    	return hasLocalChanges("");
    }
    
    public boolean hasLocalChanges(String relativePath) {
        ISVNClientAdapter client = getClientAdapter();
        
        File dir = new File(getLocalRootDir().getAbsolutePath() + relativePath);
        new RejectPathDoesNotExist().validate(dir);
        ISVNStatus[] statuses = null;
        try {
            statuses = client.getStatus(dir, true, true); //descend, all
        } catch (SVNClientException e) {
            throw new RuntimeException("Handling for SVNClientException not implemented", e);
        }
        // will exit and return true when it finds a modified file/dir
        for (int i = 0; i<statuses.length; i++) {
            ISVNStatus st = statuses[i];
            System.out.println(st.getPath() + ": TextStatus=" + st.getTextStatus() + ", PropStatus=" + st.getPropStatus());
            if (hasLocalChanges(st)) {
            	return true;
            }
        }
        return false;
    }

    /**
     * @param fileOrDirStatus from the wokring copy
     * @return true if there is something to commit according to the status.
     * 	unversion files are counted as modifications.
     */
	boolean hasLocalChanges(ISVNStatus fileOrDirStatus) {
		if (SVNStatusKind.MODIFIED.equals(fileOrDirStatus.getTextStatus())) {
		    return true; // could also check for conflicts
		}
		if (SVNStatusKind.MODIFIED.equals(fileOrDirStatus.getPropStatus())) {
		    return true;
		}
		if (SVNStatusKind.UNVERSIONED.equals(fileOrDirStatus.getTextStatus())) {
			return true;
		}
		if (SVNStatusKind.ADDED.equals(fileOrDirStatus.getTextStatus())) {
		    return true; // could also check for conflicts
		}
		return false;
	}
    
    public void synchronize() {
        ISVNClientAdapter client = getClientAdapter();
        // update
        logger.debug("Starting synchronize() with an update");
        doUpdate(client);
        // release all locks
        //TODO locks
        // add all unadded
        logger.debug("Adding all unversioned files");
        doAddAll(client);
        // commit
        if (hasLocalChanges()) {
        	logger.info("There is local changes, performing commit.");
            doCommit(client, "Testing new contents");
        } else {
        	logger.info("There is no local changes");
        }
    }

    private void doCommit(ISVNClientAdapter client, String message) {
        Commit commit = new Commit();
        commit.setDir(getLocalRootDir());
        commit.setMessage(message);
        try {
            execute(commit, client);
        } catch (BuildException be) {
            System.out.println("Could not commit, probably there were no changes.");
            be.printStackTrace();
        }
    }

    private void doAddAll(ISVNClientAdapter client) {
        FileScanner directoryScanner = new DirectoryScanner();
        directoryScanner.setBasedir(getLocalRootDir());
        FileSet fileSet = new FileSet();
        fileSet.setupDirectoryScanner(directoryScanner, project);
        fileSet.setDir(getLocalRootDir());
        Add add = new Add();
        add.addFileset(fileSet);
        execute(add, client);
    }

    private void doUpdate(ISVNClientAdapter client) {
        Update update = new Update();
        update.setDir(getLocalRootDir());
        execute(update, client);
    }
    
    private ISVNClientAdapter getClientAdapter() {
    	ISVNClientAdapter svnClient = clientProvider.getSvnClient(settings.getLogin());
        for (int i = 0; i < notifyListeners.size();i++) {
            svnClient.addNotifyListener((ISVNNotifyListener)notifyListeners.get(i));
        }        
        return svnClient;
    }
    
    
    private void execute(SvnCommand command) {
		ISVNClientAdapter svnClient = getClientAdapter();
		execute(command, svnClient);
	}
    
    private void execute(SvnCommand command, ISVNClientAdapter svnClient) {
    	if (command.getProject() == null) {
    		command.setProject(project); // dummy, might be needed for some operations
    	}
        Feedback feedback = new Feedback(command);
        svnClient.addNotifyListener(feedback);
        command.execute(svnClient);
        svnClient.removeNotifyListener(feedback);
        handleFeedback(feedback);
    }

    protected void handleFeedback(Feedback feedback) {
		// feedback not handled yet
	}

	private SVNUrl getUrl() {
        return settings.getCheckoutUrl().getUrl();
    }

    private File getLocalRootDir() {
        return settings.getWorkingCopyDirectory();
    }

	public void update() {
		doUpdate(getClientAdapter());
	}

	public void lock(String relativePath) {
		if (true) {
			throw new UnsupportedOperationException("Method PersonalWorkingCopy#lock not implemented yet.");
		}
	}

}
