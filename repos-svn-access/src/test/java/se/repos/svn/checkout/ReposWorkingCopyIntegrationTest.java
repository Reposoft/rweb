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
	
	public void testNewFileStatus() throws IOException {
		File created = new File(path, "new.txt");
		if (created.exists()) fail("Invalid test setup. The file " + created.getName() + " is already in the working copy");
		assertFalse("A file that does not exist and is not under version control is not versioned", client.isVersioned(created));
		created.createNewFile();
		assertFalse("New files are not versioned until add", client.isVersioned(created));
		assertFalse("Is a new file so it has no changes", client.hasLocalChanges(created));
	}
	
	public void testMissingFileStatus() {
		File f = new File(path, "automated-test-increment.txt");
		if (!f.exists()) fail("For this test to work the file " + f.getName() + " must be under version control");
		assertTrue("Testing with an existing working copy file", client.isVersioned(f));
		assertFalse("The existing file is not modified locally", client.hasLocalChanges(f));
		// now delete without telling the svn client
		f.delete();
		assertFalse("To identify a missing file, check (exists==false && isVersioned==true", f.exists());
		assertTrue("If the file is silently deleted, it is still versioned", client.isVersioned(f));
		assertFalse("There is nothing to check in, because the file has not been marked for removal", client.hasLocalChanges(f));
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
		client.commit("Deleted test file");
		assertFalse("The file should be gone", created.exists());
		assertFalse("Now the file name is not used anymore",	client.isVersioned(created));
	}
	
	public void testAddAndDeleteEmptyFolder() throws ConflictException, RepositoryAccessException, IOException {
		File f = new File(path, "testfolder");
		f.mkdir();
		client.add(f);
		assertTrue("Folder has been added, so it is versioned", client.isVersioned(f));
		assertTrue("There is a new folder to commit", client.hasLocalChanges());
		client.commit("Added empty folder");
		assertFalse("Folder committed already", client.hasLocalChanges());
		client.delete(f);
		assertTrue("There is a delete operation to commit", client.hasLocalChanges());
		client.commit("Deleted empty folder");
		assertFalse("Client should have deleted the folder on commit (or directly when marked for deletion?)", f.exists());
		assertFalse("Working copy is up to date", client.hasLocalChanges());
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
		File created = new File(path, "tobedeletedtwice.txt");
		created.createNewFile();
		client.add(created);
		assertTrue("The file should be added", client.isVersioned(created));
		client.commit("Added test file (testDeleteAlreadyDeletedFile)");
		created.delete();
		try {
			client.delete(created);
			fail("Should not delete already deleted file. Only clients should have this type of logic.");
		} catch (WorkingCopyAccessException e) {
			fail("Should throw IllegalArgumentException because this is definitely a programming error");
		} catch (IllegalArgumentException e) {
			// expected
		}
		created.createNewFile();
		client.delete(created);
		client.commit("Cleand up after delete test");
	}

	public void testMove() throws IOException, ConflictException, RepositoryAccessException {
		File f = new File(path, "tobemoved.txt");
		File d = new File(path, "destination.txt");
		if (f.exists()) fail ("Test setup error. File exists " + f);
		if (d.exists())fail ("Test setup error. File exists " + d);
		f.createNewFile();
		client.add(f);
		client.commit("test move");
		if (client.hasLocalChanges(f)) fail ("Test error. The file " + f + " has not been committed");
		client.move(f, d);
		assertFalse("The original file should be gone", f.exists());
		assertTrue("The original file is gone, so it has local changes", client.hasLocalChanges(f));
		assertTrue("The destination file should exist", d.exists());
		assertTrue("The destination file has local changes", client.hasLocalChanges(d));
		client.commit("test move done");
		assertFalse("After commit, there is no trace of original file", client.hasLocalChanges(f));
		assertFalse("After commit, destination file is up to date", client.hasLocalChanges(d));
		client.delete(d);
		client.commit("test move cleaned");
		if (d.exists()) fail("Test error. Could not remove destination file after test.");
	}
	
}
