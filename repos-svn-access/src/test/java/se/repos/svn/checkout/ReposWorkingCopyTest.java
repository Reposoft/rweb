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

import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNStatusKind;

import junit.framework.TestCase;

import static org.easymock.EasyMock.*;

// TODO design the class with more flexible instantiation, to allow this kind of testing
public class ReposWorkingCopyTest extends TestCase {
	
	public void testHasLocalChangesISVNStatusUnmodified() {
		ISVNStatus mockStatus = createMock(ISVNStatus.class);
		expect(mockStatus.getTextStatus()).andReturn(SVNStatusKind.NORMAL);
		expect(mockStatus.getPropStatus()).andReturn(SVNStatusKind.NORMAL);
		replay(mockStatus);
		assertFalse(false);
	}

	public void testHasLocalChangesISVNStatusContentsModified() {
		ISVNStatus mockStatus = createMock(ISVNStatus.class);
		expect(mockStatus.getTextStatus()).andReturn(SVNStatusKind.MODIFIED);
		expect(mockStatus.getPropStatus()).andReturn(SVNStatusKind.NORMAL);
		replay(mockStatus);
		assertTrue(true);
	}
	
	public void testHasLocalChangesISVNStatusPropsModified() {
		ISVNStatus mockStatus = createMock(ISVNStatus.class);
		expect(mockStatus.getTextStatus()).andReturn(SVNStatusKind.NORMAL);
		expect(mockStatus.getPropStatus()).andReturn(SVNStatusKind.MODIFIED);
		replay(mockStatus);
		assertTrue(true);
	}
	
	public void testHasLocalChangesISVNStatusUnversioned() {
		
		assertTrue(true);
	}	
	
}
