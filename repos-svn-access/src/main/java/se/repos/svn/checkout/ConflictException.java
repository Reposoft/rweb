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

/**
 * Thrown when there the local changes can not be merged with the remote ones.
 * 
 * For an update operation, this means the update has completed
 * but the working copy can not be committed until conflicts are marked resolved.
 * 
 * For a commit this means that the operation was aborted.
 * It is recommended to handle conflicts at update,
 * but never guaranteed that they can be avoided at commit
 * (because there could always be very new changes remotely).
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ConflictException extends Exception {

	private static final long serialVersionUID = 1L;

	/**
	 * @return the conflict location(s)
	 */
	public ConflictInformation[] getFiles() {
		throw new UnsupportedOperationException("Not implemented yet.");
	}

	private class ConflictingFile implements ConflictInformation {
		public File getLocalChangedFile() {
			if (true) {
				throw new UnsupportedOperationException("Method ConflictException#getLocalChangedFile not implemented yet.");
			}
			return null;
		}

		public File getLatestSharedFile() {
			if (true) {
				throw new UnsupportedOperationException("Method ConflictException#getLatestSharedFile not implemented yet.");
			}
			return null;
		}

		public RepositoryUrl getFileUrl() {
			if (true) {
				throw new UnsupportedOperationException("Method ConflictException#getFileUrl not implemented yet.");
			}
			return null;
		}
	}
}
