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

import se.repos.svn.MandatoryReposOperations;
import se.repos.svn.config.ClientConfiguration;

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
	 * Checks if a local resource can be committed.
	 * Only checks resources that have been added, 
	 * so unversioned files in a folder does not count as local changes.
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
	 * Restores the entire working copy to the revision that was retreived last time an update was done.
	 * @deprecated recursive revert is generally not efficient, because metadata folders may be gone. Use update to restore working copy.
	 */
	public void revert();
	
	/**
	 * Restores a file or folder to the version in repository.
	 * 
	 * If the argument is a folder, it will be reverted recursively. This has the same limitations
	 * as in all subversion clients, that if .svn administrative folders are gone, it can not
	 * know what the contents was at last checkout.
	 * 
	 * Use delete+update to restore to the latest version from the repository.
	 * 
	 * Reverting only modified properties is not supported: it is a rare operation,
	 * and if needed it is possible to propget the old version's properties and revert manually.
	 * @param path file to reset all changes in, or folder to recursively restore
	 */
	public void revert(File path);
	
	/**
	 * Checks if a working copy folder is a subversion "admin directory".
	 * @param path the folder
	 * @return true if the folder is a metadata folder (like ".svn")
	 */
	public boolean isMetadataFolder(File path);
	
	/**
	 * Checks global-ignores and parent folder svn:ignore property to see if an entry should be ignored.
	 * 
	 * For folders that matches an ignore pattern, but are in version control,
	 * this method returns false. That is because 'svn status' reports normal versioned status for the file.
	 * 
	 * @param path any path in the working copy, parent directory (getParent) must exist.
	 * @return true if the path should not be in version control, false if it is or should be in version control.
	 */
	public boolean isIgnore(File path);

	/**
	 * Creates a read-write model of a subversion properties for a versioned resource.
	 * 
	 * @param path The versioned file or folder
	 * @return
	 */
	public VersionedProperties getProperties(File path);
	
	/**
	 * Creates a model of the settings in the "runtime configuration area".
	 * 
	 * These settings are shared by all subversion clients for the current user on the local machine.
	 * 
	 * @return current subversion runtime configuration
	 */
	public ClientConfiguration getClientSettings();
}
