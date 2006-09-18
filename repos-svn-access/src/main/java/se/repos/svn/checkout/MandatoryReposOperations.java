/* $license_header$
 */
package se.repos.svn.checkout;

/**
 * A minimal interface to show which operations are required from a repos client application.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @see ReposWorkingCopy
 */
public interface MandatoryReposOperations {

	/**
	 * @param relativePath empty for entire local dir
	 * @return true if there are uncommitet changes in this file or directory (recursively)
	 */
	public boolean hasLocalChanges();
	
	/**
	 * Update the current working copy with the changes from the repository.
	 */
	public void update() throws ConflictException;
	
	/**
	 * Synchronize with the shared repository.
	 * The goal is to make sure that the local copy and the repository has identical contents.
	 *
	 * Conflicts are treated as unexpected errors that the application must handle.
	 * The operation is aborted if it encounters conflict during commit.
	 * User must manually inspect the files and merge the local changes into
	 * the latest shared file. Then mark the conflict resolved. Then retry the operation.
	 * 
	 * @throws ConflictException Meaning that commit was not performed because there is at least one conflict.
	 */
	public void synchronize()
		throws ConflictException;
	
	/**
	 * Make a file ready for commit again after a conflict
	 * @param conflictInformation The conflicting file
	 */
	public void markConflictResolved(ConflictInformation conflictInformation);
}
