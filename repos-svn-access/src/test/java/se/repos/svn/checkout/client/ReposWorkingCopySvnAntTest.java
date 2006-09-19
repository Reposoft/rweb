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
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.ImmutableUserCredentials;
import se.repos.svn.checkout.NotifyListener;
import se.repos.svn.checkout.TestFolder;
import se.repos.svn.checkout.client.ReposWorkingCopySvnAnt;

import junit.framework.TestCase;

import static org.easymock.EasyMock.*;

public class ReposWorkingCopySvnAntTest extends TestCase {

	// note that any test that uses this need to do replay(mockClient)
	private ReposWorkingCopySvnAnt getInstanceWithMockClient() {
		UserCredentials userCredentials = new ImmutableUserCredentials("", "");
		CheckoutSettings settings = createMock(CheckoutSettings.class);
		expect(settings.getLogin()).andReturn(userCredentials).atLeastOnce();
		expect(settings.getWorkingCopyDirectory()).andReturn(TestFolder.getNew()).atLeastOnce();
		ClientProvider clientProvider = createMock(ClientProvider.class);
		
		ISVNClientAdapter mockClient = null;
		mockClient = createMock(ISVNClientAdapter.class);
		expect(clientProvider.getSvnClient(userCredentials)).andReturn(mockClient);
		mockClient.addNotifyListener(isA(NotifyListener.class));
		expectLastCall().atLeastOnce();
		
		replay(settings, clientProvider); // but let the test replay mockClient
		
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
	
	public void testAddNotifyListener() {
		ReposWorkingCopySvnAnt wc = getInstanceWithMockClient();
		
		NotifyListener notifyListener = createMock(NotifyListener.class);
		wc.getClient().addNotifyListener(notifyListener);
		expectLastCall();
		replay(wc.getClient());
		
		wc.addNotifyListener(notifyListener);
		//does not work so well: verify(wc.getClient());
	}
	
	public void testCatchConflictAtUpdate() {
		String error = "C  C:/DOCUME~1/solsson/LOKALA~1/Temp/test/increment.txt";
		ReposWorkingCopySvnAnt w = getInstanceWithMockClient();
		NotifyListener n = w.getConflictNotifyListener();
		try {
			n.logError(error);
		} catch (Throwable e) {
			fail("Can not throw exception because the underlying SVN lib catches it, and needs to do cleanup.");
			assertTrue("The error message should contain the file name",
					e.getMessage().contains("C:/DOCUME~1/solsson/LOKALA~1/Temp/test/increment.txt"));
		}
		// every update and commit needs to do this
		try {
			ReposWorkingCopySvnAnt.Conflict.reportConflicts();
			fail("A conflict should have been detected for last commit");
		} catch (ConflictException e) {
			assertEquals("Should report one conflicting file", 1, e.getConflicts().length);
			ConflictInformation c = e.getConflicts()[0];
		}
	}
	
	public void testCatchConflictNotResolvedAtCommit() {
		String error = "svn: Commit failed (details follow): " +
			"svn: Aborting commit: 'C:\\DOCUME~1\\solsson\\LOKALA~1\\Temp\\test\\increment.txt' remains in conflict";
	}
	
}
