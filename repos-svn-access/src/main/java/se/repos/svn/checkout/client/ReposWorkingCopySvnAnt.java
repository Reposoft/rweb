/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.SVNStatusKind;

import se.repos.svn.ClientProvider;
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
	
	/**
	 * 
	 * @param clientProvider
	 * @param settings 
	 */
	public ReposWorkingCopySvnAnt(ClientProvider clientProvider, CheckoutSettings settings) {
		client = clientProvider.getSvnClient(settings.getLogin());
		this.settings = settings;
	}
	
	/**
	 * Allows callback after operations.
	 * @param notifyListener A callback implementation.
	 */
	public void addNotifyListener(NotifyListener notifyListener) {
		
	}

	public void add(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#add not implemented yet");
		}
		
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

	public void update(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#update not implemented yet");
		}
		
	}

	public boolean hasLocalChanges() {
		return hasLocalChanges(settings.getWorkingCopyDirectory());
	}

	public void markConflictResolved(ConflictInformation conflictInformation) {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#markConflictResolved not implemented yet");
		}
		
	}

	public void synchronize() throws ConflictException {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#synchronize not implemented yet");
		}
		
	}

	public void update() throws ConflictException {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#update not implemented yet");
		}
		
	}

	public void checkout() {
		if (true) {
			throw new UnsupportedOperationException("Method ReposWorkingCopySvnAnt#checkout not implemented yet");
		}
		
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
	
}
