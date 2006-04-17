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

import se.repos.svn.RepositoryUrl;

public interface ConflictInformation {

	/**
	 * @return The file that coontains the latest local verision, causing the conflict
	 */
	public File getLocalChangedFile();
	
	/**
	 * @return Local copy of the current file in the repository, 
	 * 	which is newer than the file that was last checked out (before the conflict)
	 */
	public File getLatestSharedFile();
	
	/**
	 * @return The link to the online repository file.
	 * 	Contents should be identical with {@link #getLatestSharedFile()} when the conflict occurs.
	 */
	public RepositoryUrl getFileUrl();
	
}
