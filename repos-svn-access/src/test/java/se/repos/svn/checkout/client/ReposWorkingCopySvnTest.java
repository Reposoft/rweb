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

import java.io.File;

import org.easymock.MockControl;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.SVNStatusKind;

import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.NotifyListener;
import se.repos.svn.checkout.ResourceNotVersionedException;
import se.repos.svn.checkout.client.ReposWorkingCopySvn;
import se.repos.svn.test.CheckoutSettingsForTest;

import junit.framework.TestCase;

public class ReposWorkingCopySvnTest extends TestCase {
	
	public void testHasLocalChangesISVNStatusUnmodified() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		
		MockControl statusControl = MockControl.createControl(ISVNStatus.class);
		ISVNStatus statusMock = (ISVNStatus) statusControl.getMock();
		statusMock.getTextStatus();
		statusControl.setReturnValue(SVNStatusKind.NORMAL);
		statusMock.getPropStatus();
		statusControl.setReturnValue(SVNStatusKind.NORMAL);
		statusControl.replay();
		assertFalse("There is no changes", w.hasLocalChanges(statusMock));
	}

	public void testHasLocalChangesISVNStatusContentsModified() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		
		MockControl statusControl = MockControl.createControl(ISVNStatus.class);
		ISVNStatus statusMock = (ISVNStatus) statusControl.getMock();
		statusMock.getTextStatus();
		statusControl.setReturnValue(SVNStatusKind.MODIFIED);
		statusMock.getPropStatus();
		statusControl.setReturnValue(SVNStatusKind.NORMAL);
		statusControl.replay();
		assertTrue("There is contents changes", w.hasLocalChanges(statusMock));
	}
	
	public void testHasLocalChangesISVNStatusPropsModified() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		
		MockControl statusControl = MockControl.createControl(ISVNStatus.class);
		ISVNStatus statusMock = (ISVNStatus) statusControl.getMock();
		statusMock.getTextStatus();
		statusControl.setReturnValue(SVNStatusKind.NORMAL);
		statusMock.getPropStatus();
		statusControl.setReturnValue(SVNStatusKind.MODIFIED);
		statusControl.replay();
		assertTrue("There is property changes", w.hasLocalChanges(statusMock));
	}

	public void testHasLocalChangesUnversioned() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		
		MockControl statusControl = MockControl.createControl(ISVNStatus.class);
		ISVNStatus statusMock = (ISVNStatus) statusControl.getMock();
		statusMock.getTextStatus();
		statusControl.setReturnValue(SVNStatusKind.UNVERSIONED);
		statusMock.getPropStatus();
		statusControl.setReturnValue(SVNStatusKind.NONE);
		File f = new File("a.txt");
		statusMock.getFile();
		statusControl.setReturnValue(f);
		statusControl.replay();
		try {
			w.hasLocalChanges(statusMock);
			fail("Should throw exception for files that are not versioned");
		} catch (ResourceNotVersionedException e) {
			assertEquals("Should get the path from the status object", f, e.getPath());
		}
	}	

	public void testHasLocalChangesDeleted() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		
		MockControl statusControl = MockControl.createControl(ISVNStatus.class);
		ISVNStatus statusMock = (ISVNStatus) statusControl.getMock();
		statusMock.getTextStatus();
		statusControl.setReturnValue(SVNStatusKind.DELETED);
		statusMock.getPropStatus();
		statusControl.setReturnValue(SVNStatusKind.NONE);
		statusControl.replay();
		assertTrue("The file is deleted so it has local changes", w.hasLocalChanges(statusMock));
	}	

	public void testHasLocalChangesMissing() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		
		MockControl statusControl = MockControl.createControl(ISVNStatus.class);
		ISVNStatus statusMock = (ISVNStatus) statusControl.getMock();
		statusMock.getTextStatus();
		statusControl.setReturnValue(SVNStatusKind.MISSING);
		statusMock.getPropStatus();
		statusControl.setReturnValue(SVNStatusKind.NORMAL);
		statusControl.replay();
		assertFalse("The file is missing, but there is nothing to commit until marked for removal", 
				w.hasLocalChanges(statusMock));
	}	

	public void testHasLocalChangesMissingButPropertiesChanged() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		
		MockControl statusControl = MockControl.createControl(ISVNStatus.class);
		ISVNStatus statusMock = (ISVNStatus) statusControl.getMock();
		statusMock.getTextStatus();
		statusControl.setReturnValue(SVNStatusKind.MISSING);
		statusMock.getPropStatus();
		statusControl.setReturnValue(SVNStatusKind.MODIFIED);
		statusControl.replay();
		assertTrue("The changed properties of the missing file should be committed", 
				w.hasLocalChanges(statusMock));
	}
	
	public void testStatusUnversionedParent() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		File f = new File("folder");
		
		// recursion does not go into unversioned folders
		MockControl statusControl = MockControl.createControl(ISVNStatus.class);
		ISVNStatus statusMock = (ISVNStatus) statusControl.getMock();
		statusMock.getTextStatus();
		statusControl.setReturnValue(SVNStatusKind.UNVERSIONED);
		statusMock.getFile();
		statusControl.setReturnValue(f);
		statusControl.replay();
		
		try {
			w.hasLocalChanges(f, new ISVNStatus[]{statusMock}, false);
			fail("Should have thrown exception when asking hasLocalChanges on unversioned resource");
		} catch (ResourceNotVersionedException e) {
			// expected
		}
		statusControl.verify();
	}
	
	public void testStatusNormalButContentsUnversioned() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		File parent = new File("folder");
		File child = new File(parent, "file.txt");

		// the folder will not be in the results because it has status normal
		
		MockControl sc2 = MockControl.createControl(ISVNStatus.class);
		ISVNStatus s2 = (ISVNStatus) sc2.getMock();
		s2.getTextStatus();
		sc2.setReturnValue(SVNStatusKind.UNVERSIONED, 2); // one extra call because it is a single file
		s2.getFile();
		sc2.setReturnValue(child);
		sc2.replay();
		
		assertFalse("The unversioned contents do not count as local changes",
				w.hasLocalChanges(parent, new ISVNStatus[]{s2}, false));
		sc2.verify();
	}
	
	public void testStatusModifiedAndContentsUnversioned() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		File parent = new File("folder");
		// folder
		MockControl sc1 = MockControl.createControl(ISVNStatus.class);
		ISVNStatus s1 = (ISVNStatus) sc1.getMock();
		s1.getTextStatus();
		sc1.setReturnValue(SVNStatusKind.NORMAL, 2); // checked for unversioned first, then the real check
		s1.getPropStatus();
		sc1.setReturnValue(SVNStatusKind.MODIFIED);
		sc1.replay();
		// contents
		MockControl sc2 = MockControl.createControl(ISVNStatus.class);
		ISVNStatus s2 = (ISVNStatus) sc2.getMock();
		s2.getTextStatus();
		sc2.setReturnValue(SVNStatusKind.UNVERSIONED);
		sc2.replay();		
		
		assertTrue("Has local changes because parent folder properties are modified",
			w.hasLocalChanges(parent, new ISVNStatus[]{s1, s2}, false));
		sc1.verify();
		//text status probably not asked for//sc2.verify();
	}

	public void testStatusNormalAndUnversionedPlusModifiedContents() {
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		File parent = new File("folder");
		// folder
		//status normal
		// contents
		MockControl sc2 = MockControl.createControl(ISVNStatus.class);
		ISVNStatus s2 = (ISVNStatus) sc2.getMock();
		s2.getTextStatus();
		sc2.setReturnValue(SVNStatusKind.UNVERSIONED);
		sc2.replay();		
		MockControl sc3 = MockControl.createControl(ISVNStatus.class);
		ISVNStatus s3 = (ISVNStatus) sc3.getMock();
		s3.getTextStatus();
		sc3.setReturnValue(SVNStatusKind.MODIFIED, 2);
		sc3.replay();
		
		assertTrue("Has local changes because second file is modified",
			w.hasLocalChanges(parent, new ISVNStatus[]{s2, s3}, false));
		sc2.verify();
		sc3.verify();
	}
	
	public void testCatchConflictAtUpdate() {
		String error = "C  C:/DOCUME~1/solsson/LOKALA~1/Temp/test/increment.txt";
		
		MockControl conflictHandlerControl = MockControl.createControl(ConflictHandler.class);
		ConflictHandler conflictHandler = (ConflictHandler) conflictHandlerControl.getMock();
		
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		w.setConflictHandler(conflictHandler);
		
		conflictHandler.handleConflictingFile(new File("C:/DOCUME~1/solsson/LOKALA~1/Temp/test/increment.txt"));
		conflictHandlerControl.setReturnValue(null);
		conflictHandlerControl.replay();
		
		// the new instance creates a nofifylistener by default
		NotifyListener n = w.getConflictNotifyListener();
		n.logError(error);
		// and the update method should do
		try {
			w.reportConflicts();
		} catch (ConflictException e) {
			assertEquals("Should report one conflict", 1, e.getConflicts().length);
		}
		
		conflictHandlerControl.verify();
	}
	
	public void testCatchConflictWithSpaceInPath() {
		String error = "C C:\\Repos-pe\\mina konflikter\\Ny mapp\\ny fil.txt";
		
		MockControl conflictHandlerControl = MockControl.createControl(ConflictHandler.class);
		ConflictHandler conflictHandler = (ConflictHandler) conflictHandlerControl.getMock();
		
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		w.setConflictHandler(conflictHandler);
		
		conflictHandler.handleConflictingFile(new File("C:\\Repos-pe\\mina konflikter\\Ny mapp\\ny fil.txt"));
		conflictHandlerControl.setReturnValue(null);
		conflictHandlerControl.replay();
		
		// the new instance creates a nofifylistener by default
		NotifyListener n = w.getConflictNotifyListener();
		n.logError(error);
		// and the update method should do
		try {
			w.reportConflicts();
		} catch (ConflictException e) {
			assertEquals("Should report one conflict", 1, e.getConflicts().length);
		}
		
		conflictHandlerControl.verify();
	}
	
	public void testMarkConflictResolved() throws SVNClientException {
		File target = new File("tmp.txt");
		MockControl infoControl = MockControl.createControl(ConflictInformation.class);
		ConflictInformation info = (ConflictInformation) infoControl.getMock();
		info.getTargetPath();
		infoControl.setReturnValue(target);
		infoControl.replay();
		
		MockControl clientControl = MockControl.createNiceControl(ISVNClientAdapter.class);
		ISVNClientAdapter client = (ISVNClientAdapter) clientControl.getMock();
		client.resolved(target);
		clientControl.replay();
		
		MockControl conflictControl = MockControl.createControl(ConflictHandler.class);
		ConflictHandler conflict = (ConflictHandler) conflictControl.getMock();
		conflict.afterConflictResolved(info);
		conflictControl.replay();
		
		ReposWorkingCopySvn w = new ReposWorkingCopySvn();
		w.setClientAdapter(client, new CheckoutSettingsForTest());
		w.setConflictHandler(conflict);
		w.markConflictResolved(info);
		
		infoControl.verify();
		clientControl.verify();
		conflictControl.verify();
	}
	
	public void testCatchConflictNotResolvedAtCommit() {
		String error = "svn: Commit failed (details follow): " +
			"svn: Aborting commit: 'C:\\DOCUME~1\\solsson\\LOKALA~1\\Temp\\test\\increment.txt' remains in conflict";
	}
	
}
