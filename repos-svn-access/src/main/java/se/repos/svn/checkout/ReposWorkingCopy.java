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
 * @todo switch between tasks, create tasks (branch), complete tasks (merge): make a "task-aware" client
 * @todo switch between users within the same working copy: make a "multiuser" client
 */
public interface ReposWorkingCopy extends MandatoryReposOperations {

	/**
	 * To be added to file name (before .extension) when a conflicting file 
	 * is checked out to the original name, and the conflict is marked resolved automatically.
	 */
	public static final String DEFAULT_LOCAL_RENAME_AT_CONFLICT = "_your-modified";
	
	/**
	 * Allows callback after operations.
	 * @param notifyListener A callback implementation.
	 */
	public void addNotifyListener(NotifyListener notifyListener);
	
	/**
	 * Reserve a file so that others can not change it.
	 * Ensure that the file is writable locally.
	 * Locking is never required, and only encouraged for binary files like word documents.
	 * @param relativePath path relative to local directory root, no starting '/'
	 * @param maybe change the parameter to File or an interface representing local resource
	 * @todo maybe move to the mandatory methods
	 */
	public void lock(File path);
	
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
	
	/**
	 * Restores a file or folder to the version in repository
	 * @param path file to reset all changes in, or folder to recursively restore
	 */
	public void revert(File path);
}
