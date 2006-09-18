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

/**
 * Supports the repository user's work with a local working gopy.
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
 * Preferrably conflicts are detected by doing an update() before synchronize,
 * so that the latest repository changes are inspected locally before committing.
 * 
 * Implementations should be as dumb as possible. Not do any automatic stuff that the user has not requested.
 * For example always using absolute URLs (i.e. 'switch' would be implemented using a new explicit url).
 * Any assumptions about repository structure should be placed in a {@link se.repos.svn.checkout.CheckoutSettings} implementation.
 *
 * @author Staffan Olsson
 * @since 2006-apr-11
 * @version $Id$
 * @todo switch between tasks, create tasks (branch), complete tasks (merge)
 * @todo switch between users within the same working copy.
 * @todo handle how file or dir is marked for deletion
 * @todo handle when files and directories have been moved in windows folder
 * @todo handle commit message (input) and commit errors (result)
 */
public interface ReposWorkingCopy {

	/**
	 * To be added to file name (before .extension) when a conflicting file 
	 * is checked out to the original name, and the conflict is marked resolved automatically.
	 */
	public static final String DEFAULT_LOCAL_RENAME_AT_CONFLICT = "_your_changes";
	
	/**
	 * Update the current working copy with the changes from the repository.
	 * @todo return UpdateInformation with paths and change type, same as in synchronize
	 */
	public void update() throws ConflictException;
	
	/**
	 * Reserve a file so that others can not change it.
	 * Ensure that the file is writable locally.
	 * Locking is never required, and only encouraged for binary files like word documents.
	 * @param relativePath path relative to local directory root, no starting '/'
	 * @param maybe change the parameter to File or an interface representing local resource
	 */
	public void lock(String relativePath);
	
	/**
	 * @param relativePath empty for entire local dir
	 * @return true if there are uncommitet changes in this file or directory (recursively)
	 */
	public boolean hasLocalChanges();
	
	/**
	 * Synchronize with the shared repository.
	 * <ol>
	 * <li>Release all locks</li>
	 * <li>Update, handle any conflicts as user errors</li>
	 * <li>Commit local changes</li>
	 * </ol>
	 * Conflicts are treated as unexpected errors that the application must handle.
	 * The operation is aborted if it encounters conflict,
	 * ant the user must manually inspect the files and merge the local changes into
	 * the latest shared file. Then mark the conflict resolved. Then retry the operation.
	 * 
	 * TODO return updates
	 * TODO return commits
	 * 
	 * @throws ConflictException On the first encountered conflict.
	 * 	When resolved, subsequent calls may throw a new exception on next conflict,
	 *  until the complete operation can be commited.
	 *  If a conflict occurs, update may be partially done.
	 */
	public void synchronize()
		throws ConflictException;
	
}
