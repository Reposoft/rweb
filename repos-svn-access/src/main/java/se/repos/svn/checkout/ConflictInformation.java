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
import se.repos.svn.checkout.client.ConflictHandler;

/**
 * Represents the resources needed to resolve a conflict.
 * The conflict is resolved when all the extra files are deleted,
 * when the file at the original location has no conflict parkers,
 * and when the subversion client has marked the conflict resolved.
 * 
 * See <a href="http://svnbook.red-bean.com/nightly/en/svn.tour.cycle.html#svn.tour.cycle.resolve">svnbook's guide to resolving conflicts</a> for reference on standards.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @see ConflictHandler for custoization of houw conflicts are represented in the file system
 * @see MandatoryReposOperations#markConflictResolved(ConflictInformation)
 */
public interface ConflictInformation {
	
	/**
	 * The local file that contains the user's changes.
	 * @return <code>mine</code> in SVN terminology
	 */
	public File getUserFile();
	
	/**
	 * The file that the user checked out before doing changes.
	 * @return <code>rOLDREV</code> in SVN terminology
	 */
	public File getUsedRepositoryFile();
	
	/**
	 * Local copy of the current file in the repository, 
	 * 	which is newer than the file the user checked out before the conflict
	 * @return <code>rNEWREV</code> in SVN terminology
	 */
	public File getRepositoryFile();
	
	/**
	 * The original location of the file, and the location where canges should be merged.
	 *  According to SVN standard this path is =={@link #getMergedFile()}.
	 * @return The path where the file should be when the conflict is marked resolved
	 */
	public File getTargetPath();
	
	/**
	 * The file with conflict markers in it.
	 *  In SVN standard, this is the file at the original location.
	 * @return File that can not be committed because it has conflict markup
	 */
	public File getMergedFile();
	
	/**
	 * @return The link to the online repository file.
	 * 	That file's contents is identical to {@link #getRepositoryFile()} when the conflict is reported
	 *  (except for updated <code>svn:keywords</code>)
	 */
	public RepositoryUrl getFileUrl();
	
}
