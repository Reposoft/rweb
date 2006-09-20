/* Copyright 2006 Optime data Sweden
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package se.repos.svn.checkout;

import java.io.File;

/**
 * The repos.se operations on a working copy, as an abstraction above svnClientAdapter.
 * 
 * Preferrably conflicts are detected by doing an update() before commit,
 * so that the latest repository changes are inspected locally before committing.
 * 
 * Implementations should be as dumb as possible. Not do any automatic stuff that the user has not requested.
 * For example always using absolute URLs (i.e. 'switch' would be implemented using a new explicit url).
 * Any assumptions about repository structure should be placed in a {@link se.repos.svn.checkout.CheckoutSettings} implementation.
 *
 * @author Staffan Olsson
 * @since 2006-apr-11
 * @version $Id$
 * @todo switch between tasks, create tasks (branch), complete tasks (merge): make a "task-aware" client
 * @todo switch between users within the same working copy: make a "multiuser" client
 */
public interface ReposWorkingCopy extends MandatoryReposOperations {
	
	/**
	 * Allows callback after operations.
	 * @param notifyListener A callback implementation.
	 */
	public void addNotifyListener(NotifyListener notifyListener);
	
	/**
	 * Checkout is needed only if the working copy has not been used before.
	 * Use isVersioned() to check if a working copy root dir contains checked out files.
	 * @throws RepositoryAccessException 
	 * @see MandatoryReposOperations#synchronize(String)
	 */
	public void checkout() throws RepositoryAccessException;
	
	/**
	 * Like {@link ReposWorkingCopy#update()} but for a part of the working copy.
	 * @param path Folder or file within a working copy
	 * @throws RepositoryAccessException 
	 */
	public void update(File path) throws ConflictException, RepositoryAccessException;
	
	/**
	 * Sends local changes to the repository
	 * @param commitMessage For the log
	 * @throws ConflictException The risk of this is minimal if an update is done just before every commit.
	 * @throws RepositoryAccessException 
	 */
	public void commit(String commitMessage) throws ConflictException, RepositoryAccessException;
	
	/**
	 * Checks if a local resource can be committed
	 * @param path The file or folder to check
	 * @return true if there is changes to the versioned contents, recursively for folders
	 * @see MandatoryReposOperations#hasLocalChanges()
	 */
	public boolean hasLocalChanges(File path);
	
	/**
	 * Checks if a file is under version control.
	 * If the file is status=MISSING but not marked for delete, it is still under version control.
	 * @param path file or folder
	 * @return true if the path is under version control
	 */
	public boolean isVersioned(File path);
	
	/**
	 * Reserve a file so that others can not change it.
	 * Ensure that the file is writable locally.
	 * Locking is never required, and only encouraged for binary files like word documents.
	 * @param path The resource to lock. Usually a file, because lock does not work recursively.
	 */
	public void lock(File path);
	
	/**
	 * Remove locks from file.
	 * SVN standard behaviour is to do this automatically at commit.
	 * @param path The locked resource
	 */
	public void unlock(File path);
	
	/**
	 * Add a new file in the working copy to version control
	 * For folders that are already under version control,
	 * it is invalid to do add again to add all contents recursively.
	 * Instead those files must be added one by one.
	 * @param path File or folder, for new folders all contents are also added.
	 */
	public void add(File path);
	
	/**
	 * Adds all unversioned files inside the working copy to version control.
	 * Contrary to {@link #add(File)} this can be called for a directory that is itself under version control.
	 */
	public void addAll();
	
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
	
	/**
	 * Restores a file or folder to the version in repository
	 * @param path file to reset all changes in, or folder to recursively restore
	 */
	public void revert(File path);
}
