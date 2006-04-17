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
 * Operations to keep a local copy of a workspace.
 * 
 * Before a file is changed, it should be reserved using {@see #lock()}.
 * If a file is changed when it is not locked,
 * it will be treated as optimistic locking. This means that if someone else
 * checked in a newer file, the changes will not be saved.
 * It that happens, the user needs to get the latest version of the file from the repository,
 * and manually merge the local changes to it.
 * 
 * Implementations should be as dumb as possible. 
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
	 * Update the current working copy.
	 */
	public void update();
	
	/**
	 * Reserve a file so that others can not change it.
	 * Ensure that the file is writable locally.
	 * @param relativePath path relative to local rirectory root, no starting '/'
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
	 * The operation stops at the first conflict it encounters.
	 * Application should get the latest version of the file and
	 * then mark the conflict as resolved. Local changes should be merged manually.
	 * Then retry the operation. Might give another conflict somewhere else.
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
