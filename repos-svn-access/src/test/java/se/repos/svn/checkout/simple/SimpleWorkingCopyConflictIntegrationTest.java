/* $license_header$
 */
package se.repos.svn.checkout.simple;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;

import se.repos.svn.checkout.CheckoutSettingsForTest;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.RepositoryAccessException;
import junit.framework.TestCase;

public class SimpleWorkingCopyConflictIntegrationTest extends TestCase {

	protected void setUp() throws Exception {
		super.setUp();
		System.out.println("---------- " + super.getName() + " ----------");
	}

	public void testConflictAtSynchronize() throws FileNotFoundException, IOException, RepositoryAccessException {
		CheckoutSettingsForTest s1 = new CheckoutSettingsForTest();
		CheckoutSettingsForTest s2 = new CheckoutSettingsForTest();
		SimpleWorkingCopy w1 = new SimpleWorkingCopy(s1);
		SimpleWorkingCopy w2 = new SimpleWorkingCopy(s2);
		// now both should be checked out
		File f1 = new File(s1.getWorkingCopyFolder() + "/" + SimpleWorkingCopyIntegrationTest.TEST_FILE);
		File f2 = new File(s2.getWorkingCopyFolder() + "/" + SimpleWorkingCopyIntegrationTest.TEST_FILE);
		
		int i1 = SimpleWorkingCopyIntegrationTest.increaseCounter(f1);
		int i2 = SimpleWorkingCopyIntegrationTest.increaseCounter(f2);
		i2 = SimpleWorkingCopyIntegrationTest.increaseCounter(f2);
		assertEquals("Should be the same version of the file, but with different modifications", i1, i2 - 1);
		
		// commit the first wc's change
		try {
			w1.synchronize("Commit conflict test " + i1);
		} catch (ConflictException e) {
			fail("Should not get conflict for first commit. Maybe the test is running elsewhere. Try again");
		}
		// commit the conflicting change
		ConflictInformation conflictInformation = null;
		try {
			w2.synchronize("Commit conflict test " + i2);
			fail("Should have got a conflict exception, because two identical changes have been made");
		} catch (ConflictException e) {
			listDirContents(s2.getWorkingCopyFolder());
			assertEquals("Should report one conflicting file", 1, e.getConflicts().length);
			conflictInformation = e.getConflicts()[0];
		}
		// verify the conflicting file
		// seems like javahl reports the full path in windows, and tmatesvn reports short folder names like "DOCUME~1"
		assertEquals("ConflictInformation should report the same absolute path", 
				f2.getCanonicalPath(), conflictInformation.getTargetPath().getCanonicalPath());
		
		// number 2 likes his file better
		conflictInformation.getTargetPath().delete();
		conflictInformation.getUserFile().renameTo(conflictInformation.getTargetPath());
		// now resolve the conflict
		w2.markConflictResolved(conflictInformation);
		// great, now try to commit again
		try {
			w2.synchronize("Commit after conflict resolved: " + i2);
		} catch (ConflictException e) {
			fail("Conflict has been marked resolved. Commit should do just fine.");
		}		
		// now there is no difference between local and remote
		assertFalse("File is identical with repository version and conflict is resolved", w2.hasLocalChanges());
		// verify that the conflict files have been removed
		assertFalse("HEAD file should have been removed", conflictInformation.getRepositoryFile().exists());
		listDirContents(s2.getWorkingCopyFolder());
	}

	// helper method to examine working copy state
	private void listDirContents(File workingCopyDirectory) {
		String[] files = workingCopyDirectory.list();
		for (int i=0; i < files.length; i++) {
			System.out.println(files[i]);
		}
	}

}
