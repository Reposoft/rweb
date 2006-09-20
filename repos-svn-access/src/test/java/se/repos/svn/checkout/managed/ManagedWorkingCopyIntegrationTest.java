/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;
import java.io.IOException;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.CheckoutSettingsForTest;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.RepositoryAccessException;
import junit.framework.TestCase;

public class ManagedWorkingCopyIntegrationTest extends TestCase {

	// test workingcopy path
	private File path;
	
	private ManagedWorkingCopy client;
	
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
	
	protected ManagedWorkingCopy init(CheckoutSettings settings) throws RepositoryAccessException {
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings);
		c.checkout();
		return c;
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
	
	public void testMoveAlreadyMovedFolder() throws IOException, ConflictException, RepositoryAccessException {
		File f = new File(path, "tobemoved");
		File d = new File(path, "destination");
		if (f.exists()) fail ("Test setup error. Folder exists " + f);
		if (d.exists())fail ("Test setup error. Folder exists " + d);
		f.createNewFile();
		client.add(f);
		client.commit("test move already moved");
		if (client.hasLocalChanges(f)) fail ("Test error. The file " + f + " has not been committed");
		
		f.renameTo(d);
		client.move(f, d);
		assertFalse("The original file should be gone", f.exists());
		assertTrue("The original file is gone, so it has local changes", client.hasLocalChanges(f));
		assertTrue("The destination file should exist", d.exists());
		assertTrue("The destination file has local changes", client.hasLocalChanges(d));
		client.commit("test move already moved done");
		assertFalse("After commit, there is no trace of original file", client.hasLocalChanges(f));
		assertFalse("After commit, destination file is up to date", client.hasLocalChanges(d));
		client.delete(d);
		client.commit("test move already moved cleaned");
		if (d.exists()) fail("Test error. Could not remove destination file after test.");
	}
	
}
