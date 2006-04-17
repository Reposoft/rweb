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

public class ConflictException extends Exception
	implements ConflictInformation {

	private static final long serialVersionUID = 1L;

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
