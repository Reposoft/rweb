/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

import se.repos.svn.checkout.ReposWorkingCopy;

/**
 * A more classic versioning client than {@link ReposWorkingCopy}, with fine grained explicit operations.
 *
 * Still uses {@link ReposWorkingCopy#synchronize()} in place of commit.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface ReposWorkingCopyClient extends ReposWorkingCopy {

	/**
	 * Like {@link ReposWorkingCopy#update()} but for a part of the working copy.
	 * @param path Folder or file within a working copy
	 */
	public void update(File path);
	
	/**
	 * Add a new file in the working copy to version control
	 * For folders that are already under version control,
	 * it is invalid to do add again to add all contents recursively.
	 * Instead those files must be added one by one.
	 * @param path File or folder, for new folders all contents are also added.
	 */
	public void add(File path);
	
	/**
	 * Remove a file or folder from version control.
	 * @param path To be removed from repository HEAD, 
	 * if it still exists locally it will be deleted after the operation.
	 */
	public void delete(File path);
	
	/**
	 * Mark a file or folder as moved in the repository.
	 * TODO is this operation only possible if the file is still at original location?
	 * @param from current location
	 * @param to new location
	 */
	public void move(File from, File to);
}
