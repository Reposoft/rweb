/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.MandatoryReposOperations;
import se.repos.svn.checkout.NotifyListener;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.ReposWorkingCopyFactory;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.simple.SimpleWorkingCopy;

/**
 * A basic subversion client, with extra logic for supporting common user operations.
 *
 * <ul>
 * <li>Allows {@link MandatoryReposOperations#synchronize(String)} for convenience, and {@link ReposWorkingCopy#commit(String)} for speed.
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
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @see SimpleWorkingCopy for a more automated client
 */
public class ManagedWorkingCopy implements ReposWorkingCopy {

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

	public void checkout() throws RepositoryAccessException {
		workingCopy.checkout();		
	}

	public void commit(String commitMessage) throws ConflictException, RepositoryAccessException {
		workingCopy.commit(commitMessage);
	}

	public void delete(File path) {
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

	public void move(File from, File to) {
		workingCopy.move(from, to);
	}

	public void revert(File path) {
		workingCopy.revert(path);
	}

	public void update(File path) throws ConflictException {
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

}
