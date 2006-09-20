/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.MandatoryReposOperations;
import se.repos.svn.checkout.NotifyListener;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.ReposWorkingCopyFactory;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.WorkingCopyAccessException;
import se.repos.svn.checkout.simple.SimpleWorkingCopy;

/**
 * A basic subversion client, with extra logic for supporting common user operations.
 *
 * "Managed" client means that it is assumed that the user gets some help in maintaining the working copy, so no integrity checks are needed here.
 *
 * <ul>
 * <li>Allows {@link #synchronize(String)} for convenience, and {@link #commit(String)} for speed.
 * <li>Allows move operation on an already moved file or folder
 * <li>Allows delete on an already deleted file or folder
 * </ul>
 * 
 * Before a file is changed, it can be reserved using {@see #lock(String)}.
 * If a file is changed when it is not locked, optimistic locking is used.
 * So if there are no conflicts, synchronize will not complain
 * (this goes also for non-binary files that can be automatically merged).
 * If someone checked in changes that can not be merged witht the local file,
 * a conflict will be reported.
 * It that happens, the user will have two files: the latest local file
 * and the latest from the repository.
 *
 * @todo how to handle hasLocalChanges and missing files; autodelete? revert?
 * @todo recursive lock?
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @see SimpleWorkingCopy for a more automated client
 */
public class ManagedWorkingCopy implements ReposWorkingCopy {

	final Logger logger = LoggerFactory.getLogger(this.getClass());	
	
	ReposWorkingCopy workingCopy;
	
	public ManagedWorkingCopy(CheckoutSettings settings) {
		workingCopy = ReposWorkingCopyFactory.getClient(settings);
	}

	public void add(File path) {
		workingCopy.add(path);
	}

	public void addAll() {
		workingCopy.addAll();
	}

	public void addNotifyListener(NotifyListener notifyListener) {
		workingCopy.addNotifyListener(notifyListener);
	}

	/**
	 * Note that checkout will work as an update if the working copy is already checked out.
	 */
	public void checkout() throws RepositoryAccessException {
		workingCopy.checkout();		
	}

	public void commit(String commitMessage) throws ConflictException, RepositoryAccessException {
		workingCopy.commit(commitMessage);
	}

	public void delete(File path) {
		// temporarily revert the file so it can be deleted by the client
		if (!path.exists()) {
			// must be the exact same contents to allow delete
			workingCopy.revert(path);
		}
		workingCopy.delete(path);
	}

	public boolean hasLocalChanges(File path) {
		return workingCopy.hasLocalChanges(path);
	}

	public boolean isVersioned(File path) {
		return workingCopy.isVersioned(path);
	}

	public void lock(File path) {
		workingCopy.lock(path);
	}

	/**
	 * Supports moving files that have already moved, but not folders.
	 */
	public void move(File from, File to) {
		if (!from.exists() && workingCopy.isVersioned(from)) {
			logger.info("Detected an attempt to move file that has already been moved. Restoring the source temporarily.");
			if (!to.exists()) throw new IllegalArgumentException("Neither source nor destination for move exists");
			if (to.isFile() && workingCopy.isVersioned(to)) throw new IllegalArgumentException("The destination file for move is already versioned: " + to);
			// TODO test that, if it is a folder, it has svn metadata from the old location
			if (to.renameTo(from)) {
				workingCopy.move(from, to);
			} else {
				throw new WorkingCopyAccessException("Could not restore the moved folder " + from + " from the destination " + to + ", so the versioned move can not be done");
			}
		} else {
			workingCopy.move(from, to);
		}
	}

	public void revert(File path) {
		workingCopy.revert(path);
	}

	public void update(File path) throws ConflictException, RepositoryAccessException {
		workingCopy.update(path);
	}

	public boolean hasLocalChanges() {
		return workingCopy.hasLocalChanges();
	}

	public void markConflictResolved(ConflictInformation conflictInformation) {
		workingCopy.markConflictResolved(conflictInformation);
	}

	public void synchronize(String commitMessage) throws ConflictException, RepositoryAccessException {
		workingCopy.synchronize(commitMessage);
	}

	public void update() throws ConflictException, RepositoryAccessException {
		workingCopy.update();
	}

	public void unlock(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#unlock not implemented yet");
		}
		
	}

}
