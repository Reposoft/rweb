/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

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
import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNClientException;
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

/**
 * Uses subclipse {@link http://subclipse.tigris.org/svnant.html SvnAnt} to implement the subversion operations
 *
 * This class uses the {@link http://www.slf4j.org/ slf4j} logging API.
 * See the slf4j docs on how to customize output.
 * 
 * This is a stateful implementation. The instance has its own ISVNClientAdapter,
 * which has a username and password set using {@link #setUserCredentials(UserCredentials)}.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ReposWorkingCopySvnAnt implements ReposWorkingCopy {
	
	final Logger logger = LoggerFactory.getLogger(this.getClass());	
	
	ISVNClientAdapter client;
	
	CheckoutSettings settings;
	
	//used for all Ant calls that need a Project instance
    private final Project ANTPROJECT = new Project();
	
	/**
	 * 
	 * @param clientProvider
	 * @param settings 
	 */
	public ReposWorkingCopySvnAnt(ClientProvider clientProvider, CheckoutSettings settings) {
		client = clientProvider.getSvnClient(settings.getLogin());
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
        try {
            execute(commit);
        } catch (BuildException be) {
            logger.error("Could not commit, probably there were no changes.");
            be.printStackTrace();
        }
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
        //Feedback feedback = new Feedback(command);
        //svnClient.addNotifyListener(feedback);
        command.execute(client);
        //svnClient.removeNotifyListener(feedback);
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
    
}
