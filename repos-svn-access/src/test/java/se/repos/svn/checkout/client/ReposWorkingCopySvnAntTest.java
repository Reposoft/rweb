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
package se.repos.svn.checkout.client;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNStatusKind;

import se.repos.svn.ClientProvider;
import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ImmutableUserCredentials;
import se.repos.svn.checkout.client.ReposWorkingCopySvnAnt;

import junit.framework.TestCase;

import static org.easymock.EasyMock.*;

// TODO design the class with more flexible instantiation, to allow this kind of testing
public class ReposWorkingCopySvnAntTest extends TestCase {

	private ISVNClientAdapter mockClient = null;
	private ReposWorkingCopySvnAnt getInstanceWithMockClient() {
		UserCredentials userCredentials = new ImmutableUserCredentials("", "");
		CheckoutSettings settings = createMock(CheckoutSettings.class);
		expect(settings.getLogin()).andReturn(userCredentials);
		ClientProvider clientProvider = createMock(ClientProvider.class);
		
		mockClient = createMock(ISVNClientAdapter.class);
		expect(clientProvider.getSvnClient(userCredentials)).andReturn(mockClient);
		
		return getInstance(clientProvider, settings);
	}

	private ReposWorkingCopySvnAnt getInstance(ClientProvider clientProvider, CheckoutSettings settings) {
		return new ReposWorkingCopySvnAnt(clientProvider, settings);
	}
	
	public void testHasLocalChangesISVNStatusUnmodified() {
		ReposWorkingCopySvnAnt w = getInstanceWithMockClient();
		
		ISVNStatus mockStatus = createMock(ISVNStatus.class);
		expect(mockStatus.getTextStatus()).andReturn(SVNStatusKind.NORMAL);
		expect(mockStatus.getPropStatus()).andReturn(SVNStatusKind.NORMAL);
		replay(mockStatus);
		assertFalse(w.hasLocalChanges(mockStatus));
	}

	public void testHasLocalChangesISVNStatusContentsModified() {
		ReposWorkingCopySvnAnt w = getInstanceWithMockClient();
		
		ISVNStatus mockStatus = createMock(ISVNStatus.class);
		expect(mockStatus.getTextStatus()).andReturn(SVNStatusKind.MODIFIED);
		expect(mockStatus.getPropStatus()).andReturn(SVNStatusKind.NORMAL);
		replay(mockStatus);
		assertTrue(w.hasLocalChanges(mockStatus));
	}
	
	public void testHasLocalChangesISVNStatusPropsModified() {
		ReposWorkingCopySvnAnt w = getInstanceWithMockClient();
		
		ISVNStatus mockStatus = createMock(ISVNStatus.class);
		expect(mockStatus.getTextStatus()).andReturn(SVNStatusKind.NORMAL);
		expect(mockStatus.getPropStatus()).andReturn(SVNStatusKind.MODIFIED);
		replay(mockStatus);
		assertTrue(w.hasLocalChanges(mockStatus));
	}
	
	/* public void testHasLocalChangesISVNStatusUnversioned() {
		
		assertTrue(true);
	} */
	
}
