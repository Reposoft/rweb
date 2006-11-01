/* $license_header$
 */
package se.repos.svn;

import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.RepositoryAccessException;

/**
 * A minimal interface to show which operations are required from a repos client application.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @see se.repos.svn.checkout.ReposWorkingCopy
 */
public interface MandatoryReposOperations {

	/**
	 * Checks for changes that have not been committed.
	 * 
	 * Checks recursively, but only resources that have been added.
	 * TODO investigate if, like in TortoiseSVN and SmartSVN, it should be an option to count new files as changes.
	 * 
	 * @param relativePath empty for entire local dir
	 * @return true if there are uncommitet changes in this file or directory (recursively)
	 */
	public boolean hasLocalChanges();
	
	/**
	 * Update the current working copy with the changes from the repository.
	 * @throws RepositoryAccessException if the online access fails
	 */
	public void update() throws ConflictException, RepositoryAccessException;
	
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
	 * @throws RepositoryAccessException if the online access fails
	 */
	public void synchronize(String commitMessage)
		throws ConflictException, RepositoryAccessException;
	
	/**
	 * Make a file ready for commit again after a conflict.
	 * @param conflictInformation The conflicting file
	 */
	public void markConflictResolved(ConflictInformation conflictInformation);
}
