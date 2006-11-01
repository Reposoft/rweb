/* $license_header$
 */
package se.repos.svn.checkout.commontests;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.test.CheckoutSettingsForTest;
import junit.framework.TestCase;

public class ConflictHandlingIntegrationTest extends TestCase {

	static final String TEST_FILE = CheckoutUpdateCommitIntegrationTest.TEST_FILE;
	
	static int increaseCounter(File testFile) throws IOException, FileNotFoundException {
		return CheckoutUpdateCommitIntegrationTest.increaseCounter(testFile);
	}

	public void testConflictAtSynchronize() throws FileNotFoundException, IOException, RepositoryAccessException {
		// get two different temp working copy folders
		CheckoutSettings s1 = new CheckoutSettingsForTest();
		CheckoutSettings s2 = new CheckoutSettingsForTest();
		
		ReposWorkingCopy w1 = AllTests.getClient(s1, getName());
		w1.checkout();
		ReposWorkingCopy w2 = AllTests.getClient(s2, getName());
		w2.checkout();
		
		// now both should be checked out
		File f1 = new File(s1.getWorkingCopyFolder(), TEST_FILE);
		File f2 = new File(s2.getWorkingCopyFolder(), TEST_FILE);
		
		// make two different modifications
		int i1 = increaseCounter(f1);
		int i2 = increaseCounter(f2);
		i2 = increaseCounter(f2);
		assertEquals("Should be the same version of the file, but with different modifications", i1, i2 - 1);
		
		// commit the first wc's change
		try {
			w1.commit("Commit first working copy in conflict test " + i1);
		} catch (ConflictException e) {
			fail("Should not get conflict for first commit. Maybe the test is running elsewhere. Try again");
		}
		
		// update to the other working copy
		ConflictInformation conflictInformation = null;
		try {
			w2.update();
			fail("Should have got a conflict exception, because two identical changes have been made");
		} catch (ConflictException e) {
			System.out.println("The files created by a conflict: ");
			listDirContents(s2.getWorkingCopyFolder());
			assertEquals("Should report one conflicting file", 1, e.getConflicts().length);
			conflictInformation = e.getConflicts()[0];
		}
		// verify the conflicting file
		// using canonicalPath because seems like javahl reports the full path in windows, and tmatesvn reports short folder names like "DOCUME~1"
		assertEquals("ConflictInformation should report the path of the versioned file", 
				f2.getCanonicalPath(), conflictInformation.getTargetPath().getCanonicalPath());
		
		// try to commit, should fail as long as there is conflicts
		
		
		// number 2 likes his file better
		conflictInformation.getTargetPath().delete();
		conflictInformation.getUserFile().renameTo(conflictInformation.getTargetPath());
		// now resolve the conflict
		w2.markConflictResolved(conflictInformation);
		// great, now try to commit again
		try {
			w2.commit("Commit working copy two, after conflict resolved: " + i2);
		} catch (ConflictException e) {
			fail("Conflict has been marked resolved. Commit should do just fine.");
		}		
		// now there is no difference between local and remote
		assertFalse("File is identical with repository version and conflict is resolved", w2.hasLocalChanges());
		// verify that the conflict files have been removed
		assertFalse("Conflict file containning HEAD contents should have been removed", conflictInformation.getRepositoryFile().exists());
	}

	// helper method to examine working copy state
	private void listDirContents(File workingCopyDirectory) {
		String[] files = workingCopyDirectory.list();
		for (int i=0; i < files.length; i++) {
			System.out.println(files[i]);
		}
	}

}
