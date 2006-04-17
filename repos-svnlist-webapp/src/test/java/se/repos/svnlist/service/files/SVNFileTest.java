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

import org.tmatesoft.svn.core.SVNDirEntry;
import org.tmatesoft.svn.core.SVNException;
import org.tmatesoft.svn.core.SVNNodeKind;
import org.tmatesoft.svn.core.SVNURL;

import junit.framework.TestCase;

public class SVNFileTest extends TestCase {

	SVNDirEntry fileStub = new SVNDirEntry(null, null, null, 0, false, 0, null, null) {
		@Override public String getName() {
			return "theFileName";
		}
		@Override
		public SVNNodeKind getKind() {
			return SVNNodeKind.FILE;
		}
		@Override
		public SVNURL getURL() {
			try {
				return SVNURL.parseURIEncoded("http://example.c/theFileName");
			} catch (SVNException e) {
				// TODO auto-generated
				throw new RuntimeException("SVNException handling missing", e);
			}
		}
		
	};
	
	/*
	 * Test method for 'se.repos.svnlist.service.files.SVNFile.getFilename()'
	 */
	public void testGetFilename() {
		RepositoryFile file = new SVNFile(fileStub);
		assertEquals("theFileName", file.getFilename());
	}

	/*
	 * Test method for 'se.repos.svnlist.service.files.AbstractFile.getDescription()'
	 */
	public void testGetDescription() {
		RepositoryEntry file = new SVNFile(fileStub);
		assertEquals("theFileName", file.getDescription());	
	}
	
	public void testGetURL() throws IOException {
		RepositoryEntry file = new SVNFile(fileStub);
		assertNotNull(file.getURL());
	}

}
