/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;
import java.io.IOException;

import junit.framework.TestCase;

/**
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo when there is more than one working copy implementation, make this abstract and use to test all implementations
 */
public class ReposWorkingCopyIntegrationTest extends TestCase {

	// test workingcopy path
	private File path;
	
	private ReposWorkingCopy client;
	
	public void setUp() throws Exception {
		super.setUp();
		CheckoutSettings settings = new CheckoutSettingsForTest();
		path = settings.getWorkingCopyDirectory();
		client = init(settings);
	}
	
	public void tearDown() {
		path = null;
		client = null;
		CheckoutSettingsForTest.tearDown();
	}
	
	protected ReposWorkingCopy init(CheckoutSettings settings) throws RepositoryAccessException {
		ReposWorkingCopy c = ReposWorkingCopyFactory.getClient(settings);
		c.checkout();
		return c;
	}
	
	public void testNewFileStatus() {
		File created = new File(path, "new.txt");
		if (created.exists()) fail("Invalid test setup. The file " + created.getName() + " is already in the working copy");
		assertFalse("Is a new file", client.isVersioned(created));
		assertFalse("Is a new file so it has no changes", client.hasLocalChanges(created));
	}
	
	public void testAddAndDelete() throws IOException, ConflictException, RepositoryAccessException {
		File created = new File(path, "tobedeleted.txt");
		if (created.exists()) fail("Invalid test setup. The file " + created.getName() + " is already in the working copy");
		created.createNewFile();
		client.add(created);
		assertTrue("The file should be added", client.isVersioned(created));
		try {
			client.delete(created);
			fail("Should say that the file can not be deleted because it has local modifications");
		} catch (WorkingCopyAccessException e) {
			// expected
		}
		client.commit("Added test file");
		client.delete(created);
		assertFalse("The file should be deleted by the client", created.exists());
		assertTrue("Should still say isVersioned=true for a deleted file, because a new file with the same name can not be created until after commit", 
				client.isVersioned(created));
		client.commit("Deleted test file");
		assertFalse("The file should be gone", created.exists());
		assertFalse("Now the file name is not used anymore",	client.isVersioned(created));
	}	
	
	public void testAddOutsideWorkingCopy() throws IOException {
		File tmp = File.createTempFile("PersonalWorkingCopyTestFile", "file");
		tmp.deleteOnExit();
		try {
			client.add(tmp);
			fail("Should have reported error because the new folder is not inside a working copy");
		} catch (WorkingCopyAccessException e) {
			// expected
		}
	}
	
	public void testDeleteAlreadyDeletedFile() throws IOException, ConflictException, RepositoryAccessException {
		File created = new File(path, "tobedeleted.txt");
		created.createNewFile();
		client.add(created);
		assertTrue("The file should be added", client.isVersioned(created));
		client.commit("Added test file (testDeleteAlreadyDeletedFile)");
		created.delete();
		client.delete(created);
		assertTrue("Should mark the file for deletion, even if it is gone already, " +
				"and report that the path hasLocalChanges", client.hasLocalChanges(created));
		assertFalse("The client may need to temporarily create the file, but it should be removed again",
				created.exists());
		client.commit("Deleted test file (testDeleteAlreadyDeletedFile)");
		assertFalse("The file should be gone", created.exists());
		assertFalse("Now the file name is not used anymore", client.isVersioned(created));
	}

	public void testMove() {
		fail("Not yet implemented");
	}

	public void testMoveAlreadyMovedFolder() {
		fail("Not yet implemented");
	}
	
}
