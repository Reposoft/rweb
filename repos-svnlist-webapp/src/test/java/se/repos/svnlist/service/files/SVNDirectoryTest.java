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

import junit.framework.TestCase;

public class SVNDirectoryTest extends TestCase {

	private SVNDirEntry stubDir = new SVNDirEntry(null, null, null, 0, false, 0, null, null) {
		
	};

	/*
	 * Test method for 'se.repos.svnlist.service.files.SVNDirectory.getURL()'
	 */
	public void testGetURL() throws IOException {
		SVNDirectory dir = new SVNDirectory(stubDir );
		assertNotNull(dir.getURL());
	}

	/*
	 * Test method for 'se.repos.svnlist.service.files.SVNDirectory.getDescription()'
	 */
	public void testGetDescription() {
		SVNDirectory dir = new SVNDirectory(stubDir );
		assertNotNull(dir.getDescription());
	}

}
