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
import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNStatusKind;

import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.NotifyListener;
import se.repos.svn.checkout.client.ReposWorkingCopySvnAnt;

import junit.framework.TestCase;

public class ReposWorkingCopySvnAntTest extends TestCase {

	public void testHasLocalChangesISVNStatusUnmodified() {
		ReposWorkingCopySvnAnt w = new ReposWorkingCopySvnAnt();
		
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
		ReposWorkingCopySvnAnt w = new ReposWorkingCopySvnAnt();
		
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
		ReposWorkingCopySvnAnt w = new ReposWorkingCopySvnAnt();
		
		MockControl statusControl = MockControl.createControl(ISVNStatus.class);
		ISVNStatus statusMock = (ISVNStatus) statusControl.getMock();
		statusMock.getTextStatus();
		statusControl.setReturnValue(SVNStatusKind.NORMAL);
		statusMock.getPropStatus();
		statusControl.setReturnValue(SVNStatusKind.MODIFIED);
		statusControl.replay();
		assertTrue("There is property changes", w.hasLocalChanges(statusMock));
	}
	
	public void testCatchConflictAtUpdate() {
		String error = "C  C:/DOCUME~1/solsson/LOKALA~1/Temp/test/increment.txt";
		
		MockControl conflictHandlerControl = MockControl.createControl(ConflictHandler.class);
		ConflictHandler conflictHandler = (ConflictHandler) conflictHandlerControl.getMock();
		
		ReposWorkingCopySvnAnt w = new ReposWorkingCopySvnAnt();
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
	
	public void testCatchConflictNotResolvedAtCommit() {
		String error = "svn: Commit failed (details follow): " +
			"svn: Aborting commit: 'C:\\DOCUME~1\\solsson\\LOKALA~1\\Temp\\test\\increment.txt' remains in conflict";
	}
	
}
