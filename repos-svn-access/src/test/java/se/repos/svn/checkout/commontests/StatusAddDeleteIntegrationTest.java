/* $license_header$
 */
package se.repos.svn.checkout.commontests;

import java.io.File;
import java.io.IOException;

import junit.framework.TestCase;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.WorkingCopyAccessException;
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
public class StatusAddDeleteIntegrationTest extends TestCase {

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
	
	public void testNewFileStatus() throws IOException {
		File created = new File(path, "new.txt");
		if (created.exists()) fail("Invalid test setup. The file " + created.getName() + " is already in the working copy");
		assertFalse("A file that does not exist and is not under version control is not versioned", client.isVersioned(created));
		created.createNewFile();
		assertFalse("New files are not versioned until add", client.isVersioned(created));
		assertFalse("Is a new file so it has no changes", client.hasLocalChanges(created));
	}
	
	public void testNewFolderStatus() throws IOException {
		File d1 = new File(path, "d1");
		d1.mkdir();
		File d2 = new File(d1, "d2");
		d2.mkdir();
		File f3 = new File(d2, "f3.txt");
		f3.createNewFile();
		assertFalse("Folder 1 shouldn't be versioned", client.isVersioned(d1));
		assertFalse("d1 not versioned -> no local changes", client.hasLocalChanges(d1));
		assertFalse("Subfolder shouldn't be versioned, but it should be possible to ask even when parent is not versioned", client.isVersioned(d2));
		assertFalse("Subfolder not versioned -> no local changes", client.hasLocalChanges(d2));
		assertFalse("File in subfolder shouldn't be versioned", client.isVersioned(f3));
		assertFalse("Folder not versioned -> file has no local changes", client.hasLocalChanges(f3));
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
		File created = new File(path, "to be deleted.txt" + System.currentTimeMillis());
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
		// create an empty folder too
		File folder = new File(path, "new folder " + System.currentTimeMillis());
		folder.mkdir();
		client.add(folder);
		assertTrue("The new folder should be added", client.isVersioned(folder));
		// do the commit
		client.commit("Added test file");
		// delete it all
		client.delete(created);
		client.delete(folder);
		assertFalse("The file should be deleted by the client", created.exists());
		assertTrue("The folder should be marked for delete but not deleted until commit", folder.exists());
		client.commit("Deleted test file and folder");
		assertFalse("The file should be gone", created.exists());
		assertFalse("The deleted folder should be gone after commit", folder.exists());
		assertFalse("Now the file name is not used anymore", client.isVersioned(created));
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
		File created = new File(path, "tobedeleted" + System.currentTimeMillis());
		created.createNewFile();
		client.add(created);
		assertTrue("The file should be added", client.isVersioned(created));
		client.commit("Added test file (testDeleteAlreadyDeletedFile)");
		created.delete(); // remove the file in file system
		try {
			client.delete(created);
		} catch (IllegalArgumentException e) {
			fail("All svn clients should be able to mark a missing file for deletion.");
		}
		assertTrue("Now the file has local changes", client.hasLocalChanges(created));
		assertFalse("The file should be gone from the file system immediately after svn delete", created.exists());
		client.commit("Delete test done");
	}
	
	public void testDeleteFolderContainingNewFile() throws IOException, ConflictException, RepositoryAccessException {
		File created = new File(path, "tobedeleted" + System.currentTimeMillis());
		created.mkdir();
		client.add(created);
		assertTrue("The folder should have been added", client.isVersioned(created));
		client.commit("Added test folder (testDeleteFolderContainingNewFile)");
		
		File child = new File(created, "nonversioned.txt");
		child.createNewFile();
		try {
			client.delete(created);
			fail("Should have thrown WorkingCopyAccessExcption because there are local changes in the folder");
		} catch (WorkingCopyAccessException e) {
			// expected
		}
		assertTrue("Should have removed the folder with the new file", client.isVersioned(created));
		
		child.delete();
		created.delete();
		client.delete(created);
		client.commit("Deleted test folder");
		assertFalse("Delete should be successful when the folder has been manually deleted", client.isVersioned(created));
	}
	
	public void testAddAllRespectsGlobalIgnores() {
		// TODO
	}
	
	public void testAddIgnoredFolderAndThenContents() {
		// TODO
		// theFolder = some name that matches an svn:ignore or global ignore
		// force add using add(theFolder)
		// add contents using addNew(theFolder), works even if the folder is already versioned
	}
	
}
