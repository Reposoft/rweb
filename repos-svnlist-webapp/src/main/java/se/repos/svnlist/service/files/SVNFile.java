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
package se.repos.svnlist.service.files;

import java.io.IOException;
import java.net.URL;

import org.tmatesoft.svn.core.SVNDirEntry;

import se.repos.validation.ValidationRule;

public class SVNFile extends AbstractFile {

	ValidationRule<SVNDirEntry> acceptedEntryType = new RejectEntryNotAFile();
	
	private SVNDirEntry file;
	
	public SVNFile(SVNDirEntry file) {
		acceptedEntryType.validate(file);
		this.file = file;
	}
	
	@Override
	public String getFilename() {
		return file.getName();
	}

	@Override
	public URL getURL() throws IOException {
		return new URL(file.getURL().toString());
	}

}
