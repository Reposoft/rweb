/* $license_header$
 */
package se.repos.svn.checkout.commontests;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.io.Writer;

import junit.framework.TestCase;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.ResourceParentNotVersionedException;
import se.repos.svn.test.CheckoutSettingsForTest;

/**
 * Tests basic operations like checkout, commit, update, add, move, delete and status.
 * 
 * If this tests does not pass, other integration test will also fail.
 * 
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo when there is more than one working copy implementation, make this abstract and use to test all implementations
 */
public class MoveCopyRevertIntegrationTest extends TestCase {

	// test workingcopy path
	private File path;
	
	private ReposWorkingCopy client;
	
	public void setUp() throws Exception {
		super.setUp();
		CheckoutSettings settings = new CheckoutSettingsForTest();
		path = settings.getWorkingCopyFolder();
		client = AllTests.getClient(settings, getName());
		client.checkout();
	}
	
	public void tearDown() {
		path = null;
		client = null;
		CheckoutSettingsForTest.tearDown();
	}

	public void testMove() throws IOException, ConflictException, RepositoryAccessException {
		File f = new File(path, getName() + "moved.txt" + System.currentTimeMillis());
		File d = new File(path, getName() + "destination.txt" + System.currentTimeMillis());
		f.createNewFile();
		client.add(f);
		client.commit(path, "test move");
		if (client.hasLocalChanges(f)) fail ("Test error. The file " + f + " has not been committed");
		// move is copy + delete
		client.copy(f, d);
		client.delete(f);
		assertFalse("The original file should be gone", f.exists());
		assertTrue("The original file is gone, so it has local changes", client.hasLocalChanges(f));
		assertTrue("The destination file should exist", d.exists());
		assertTrue("The destination file has local changes", client.hasLocalChanges(d));
		client.commit(path, "test move done");
		assertFalse("After commit, moved file should be gone", f.exists());
		assertFalse("After commit, old location should not be versioned", client.isVersioned(f));
		assertFalse("After commit, destination file is up to date", client.hasLocalChanges(d));
		client.delete(d);
		client.commit(path, "test move cleaned");
		if (d.exists()) fail("Test error. Could not remove destination file after test.");
	}

	public void testRevertRecursively() throws IOException, ConflictException, RepositoryAccessException {
		assertFalse(client.hasLocalChanges());
		File f = new File(path, "newfile" + System.currentTimeMillis());
		f.createNewFile();
		client.add(f);
		File d0 = new File(path, "temp folder " + System.currentTimeMillis());
		d0.mkdir();
		client.add(d0);
		client.commit(path, getName() + " Created new file that will soon be changed, and a folder that will be deleted");
		Writer w = new BufferedWriter(new FileWriter(f));
		w.write("Changed contents");
		w.flush();
		w.close();
		assertTrue("File status should be 'modified'", client.hasLocalChanges(f));
		client.delete(d0);
		d0.delete();
		
		File d = new File(path, "newfolder" + System.currentTimeMillis());
		d.mkdir();
		File df = new File(d, "newfolder" + System.currentTimeMillis());
		df.createNewFile();
		client.add(d); // not recursive
		client.add(df);
		assertTrue("Contents of folder should be under version control", client.isVersioned(df));
		assertTrue("The added folder should need commit", client.hasLocalChanges(d));
		
		// now revert at working copy root
		client.revert(d.getParentFile());
		assertFalse("File is not changed anymore", client.hasLocalChanges(f));
		assertTrue("File is still versioned after revert", client.isVersioned(f));
		assertEquals("File should be empty", 0, f.length());
		assertTrue("The empty folder should have been restored", d0.exists());
		assertFalse("The added directory is not versioned anymore", client.isVersioned(d));
		try {
			assertFalse("The file inside the added directory is not versioned", client.isVersioned(df));
			fail("Can not check versioned inside a non-versioned folder");
		} catch (ResourceParentNotVersionedException e) {
			assertEquals("Exception thrown for the parent folder", d.getName(), e.getPath().getName());
		}
		// clean up
		client.delete(f);
		client.delete(d0);
		client.commit(path, "Deleted test file");
	}
	
}
