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
 * The behaviour of this client mimics that of the standard svn command line client.
 * <p>
 * Implementations should be as dumb as possible. Not do any automatic stuff that the user has not requested.
 * Any assumptions about repository structure (such as user accounts, home folder etc)
 * should be placed in a {@link se.repos.svn.checkout.CheckoutSettings} implementation.
 * <p>
 * Preferrably conflicts are detected by doing an update() before commit,
 * so that the latest repository changes are inspected locally before committing.
 * <p>
 * Error handling:
 * <ol>
 * <li>Unexpected conditions with {@link java.io.File}s, like file missing, causes {@link IllegalArgumentException}</li>
 * <li>Versioning operation not allowed or failed causes {@link WorkingCopyAccessException}</lo>
 * <li>Access to repository failed or invalid causes {@link RepositoryAccessException}</li>
 * </ol>
 * The first two are considered logical errors.
 * The third is a checked exception, because it can be temporary and the application can recover from it.
 * The implementations of this interface assume that the layer above is a well tested application,
 * so no status checks are made for case 2 before an operation is carried out (to avoid extra svn operations).
 *
 * @author Staffan Olsson
 * @since 2006-apr-11
 * @version $Id$
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
	 * @throws RepositoryAccessException if the connection to the repository or the file transfer is not successful
	 * @throws IllegalStateException if the working copy folder is not empty
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
	public void commit(File path, String commitMessage) throws ConflictException, RepositoryAccessException;
	
	/**
	 * Checks if a local resource can be committed.
	 * 
	 * Only checks resources that have been added, so unversioned files in a folder does not count as local changes.
	 * A path that is missing has local changes if it is marked for deletion, but not if it is only missing.
	 * <p>
	 * The result of this operation is undefined if 'path' is not {@link #isVersioned(File) versioned}.
	 * It would be intuitive to return false, but some subversion clients allow new files
	 * to count as local changes, and we want to leave that decision to the applicaiton layer.
	 * There is currently no method in this API to check for new unversioned files recursively.
	 * 
	 * @param path The file or folder to check
	 * @return true if there is changes to the versioned contents, recursively for folders
	 * @throws ResourceNotVersionedException if the path is not under version control,
	 * check with {@link #isVersioned(File)} before calling this method if unsure.
	 * @see MandatoryReposOperations#hasLocalChanges()
	 */
	public boolean hasLocalChanges(File path) throws ResourceNotVersionedException;
	
	/**
	 * Checks if a file is under version control.
	 * If the file is gone (status=MISSING) but not marked for delete, it is still under version control.
	 * Always returns false if parent path is not under version control.
	 * @param path file or folder
	 * @return true if the path is under version control
	 * @throws ResourceParentNotVersionedException if the parent path is not versioned,
	 *  to reflect the svn client command line warning, and work like {@link #add(File)}.
	 */
	public boolean isVersioned(File path) throws ResourceParentNotVersionedException;
	
	/**
	 * Returns paths to all unversioned files or folders inside a versioned folder.
	 * 
	 * This mirrors the behaviour of 'svn status', looking for unversioned files.
	 * If an unversioned folder is listed, any contents of it are by definition
	 * never in version control, so they will not be listed
	 * (recursion does not go into unversioned folders).
	 * 
	 * @param path a folder that is versioned
	 * @return every unversioned entry, recursively from path, absolute paths.
	 *  Never returns null so if result length is 0 there are no unversioned files or folders.
	 */
	public File[] getUnversionedContents(File path);
	
	/**
	 * Adds a file or folder in the working copy to version control.
	 * 
	 * Throws an excetion if the path is already under version control.
	 * The parent of the path must be versioned.
	 * <p>
	 * Forced add, meaning that ignore patterns are not overridden.
	 * 
	 * @param path File or folder, for new folders all contents are also added.
	 * @throws WorkingCopyAccessException if the path is already under version control
	 * @throws ResourceParentNotVersionedException if the path is not in a working copy folder
	 */
	public void add(File path) throws WorkingCopyAccessException, ResourceParentNotVersionedException;
	
	/**
	 * Adds all unversioned files inside a folder to version control, respecting ignores.
	 * 
	 * Files or folders that match an svn:ignore patteron of their parent folder are not added.
	 * Files or folders that match a global-ignores entry are not added.
	 * <p>
	 * Contrary to {@link #add(File)} this can be called for a file or foldar that is already added.
	 * 
	 * @param path The path to add to version control recursively. Parent must be versioned.
	 */
	public void addNew(File path);
	
	/**
	 * Removes a file or folder from version control.
	 * 
	 * Note that if the folder has unversioned files or local modifications
	 * it can not be deleted (this is the default subversion client behaviour).
	 * The operation is naturally recursive. Upon the first path that is not valid for delete,
	 * the corresponding exception is thrown with the invalid path.
	 * That means that with repeated calls to this method, surrounded by try-catch, 
	 * all unversioned and modified children can be identified.
	 * <p>
	 * A folder with new contents that really should be deleted can be removed
	 * with a normal file system operation, then deleted with this method.
	 * 
	 * @param path To be removed from repository HEAD, 
	 * if it still exists locally it will be deleted after the operation.
	 * @throws ResourceNotVersionedException if the path is not versioned or has contents that are not versioned
	 * @throws ResourceHasLocalChangesException if the path or any sub-path has local modifications
	 * @see #isVersioned(File)
	 * @see #hasLocalChanges(File)
	 */
	public void delete(File path) throws ResourceNotVersionedException, ResourceHasLocalChangesException;
	
	/**
	 * Marks a copy of a file or folder in the working copy, which means a cheap copy in the repository.
	 * 
	 * This interface is kept as small as possible.
	 * Move operation is the same as copy+delete original.
	 * Combining these two operations give more fine grained error handling than a single move operation.
	 * <p>
	 * To copy to a location where something was just removed from version control,
	 * a commit is needed before the location can be used.
	 * 
	 * @param from current location, must exist and can NOT have local modifications.
	 * @param to new location, can not exist or be versioned
	 */
	public void copy(File from, File to);
	
	/**
	 * Restores a file or folder to the version in repository.
	 * 
	 * If the argument is a folder, it will be reverted recursively. This has the same limitations
	 * as in all subversion clients, that if .svn administrative folders are gone, it can not
	 * know what the contents was at last checkout.
	 * <p>
	 * Use delete+update to restore to the latest version from the repository.
	 * <p>
	 * Reverting only modified properties is not supported: it is a rare operation,
	 * and if needed it is possible to propget the old version's properties and revert manually.
	 * 
	 * @param path file to reset all changes in, or folder to recursively restore
	 */
	public void revert(File path);
	
	/**
	 * Reserve a file so that others can not change it.
	 * Ensure that the file is writable locally.
	 * Locking is never required, and only encouraged for binary files like word documents.
	 * 
	 * @param path The resource to lock. Usually a file, because lock does not work recursively.
	 * @param lockMessage The comment that will be shown to other users.
	 */
	public void lock(File path, String lockMessage);
	
	/**
	 * Remove locks from file.
	 * SVN standard behaviour is to do this automatically at commit.
	 * 
	 * @param path The locked resource
	 */
	public void unlock(File path);
	
	/**
	 * Checks if a working copy folder is a subversion "administrative area".
	 * 
	 * @param path the folder
	 * @return true if the folder is a metadata folder (like ".svn")
	 */
	public boolean isAdministrativeFolder(File path);
	
	/**
	 * Checks global-ignores and parent folder svn:ignore property to see if an entry should be ignored.
	 * 
	 * For folders that matches an ignore pattern, but are in version control,
	 * this method returns false. That is because 'svn status' reports normal versioned status for the file.
	 * <p>
	 * Administrative folder is always ignore.
	 * 
	 * @param path any path in the working copy, must exist, parent directory (getParent) must be versioned.
	 * @return true if the path should not be in version control, false if it is or should be in version control.
	 */
	public boolean isIgnore(File path);

	/**
	 * Provides access to svn properties as name-value pairs.
	 * 
	 * @param path The versioned file or folder
	 * @return read-write access to the properties of the path argument
	 * @throws IllegalArgumentException if the path does not exist or is not versioned
	 */
	public VersionedProperties getProperties(File path);	
	
	/**
	 * Creates a read-write model of a subversion properties for a versioned resource.
	 * 
	 * @param file The versioned file
	 * @return SVN properties for the file
	 * @throws IllegalArgumentException if the file is a folder or does not exist
	 */
	public VersionedFileProperties getPropertiesForFile(File file);
	
	/**
	 * Creates a read-write model of a subversion properties for a versioned resource.
	 * 
	 * @param folder The versioned folder
	 * @return SVN properties for the folder
	 * @throws IllegalArgumentException if the folder is a file or does not exist
	 */
	public VersionedFolderProperties getPropertiesForFolder(File folder);
	
	/**
	 * Creates a model of the settings in the "runtime configuration area".
	 * <p>
	 * These settings are shared by all subversion clients for the current user on the local machine.
	 * 
	 * @return current subversion runtime configuration.
	 * 	If the runtime configuration requires some kind of initialization,
	 *  this method makes sure it is initalized before returned.
	 */
	public ClientConfiguration getClientConfiguration();
}
