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
import se.repos.svn.checkout.ResourceHasLocalChangesException;
import se.repos.svn.checkout.ResourceNotVersionedException;
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
	
	public void testWorkingCopyRootFolderStatus() {
		assertTrue("Working copy root folder should of course be versioned", client.isVersioned(path));
		assertFalse("After checkout working copy has no changes", client.hasLocalChanges());
		assertFalse("After checkout working copy root folder has no changes", client.hasLocalChanges(path));
	}
	
	public void testFileOutsideWorkingCopy() throws IOException {
		File tmp = File.createTempFile("PersonalWorkingCopyTestFile", "file");
		tmp.deleteOnExit();
		assertFalse("folder that is outside working copy should still report not versioned", client.isVersioned(tmp));
		try {
			client.add(tmp);
			fail("Should have reported error because the new folder is not inside a working copy");
		} catch (WorkingCopyAccessException e) {
			// expected, or maybe any runtime exception
		}
		tmp.delete();
	}
	
	public void testNewFileStatus() throws IOException {
		File created = new File(path, "new.txt");
		if (created.exists()) fail("Invalid test setup. The file " + created.getName() + " is already in the working copy");
		assertFalse("A file that does not exist and is not under version control is not versioned", client.isVersioned(created));
		try {
			client.hasLocalChanges(created);
			fail("Should not be able to check status on a file that is not versioned, regardless if it exists or not");
		} catch (ResourceNotVersionedException e) { 
			assertEquals("The exception should say which file it is that is not versioned", created, e.getPath());
		}
		
		created.createNewFile();
		assertFalse("New files are not versioned until add", client.isVersioned(created));
		try {
			client.hasLocalChanges(created);
			fail("Should not be able to check status on a file that is not versioned, even if it exists");
		} catch (ResourceNotVersionedException e) { 
			assertEquals("The exception should say which file it is that is not versioned", created, e.getPath());
		}
		created.delete();
	}
	
	public void testNewFolderStatus() throws IOException {
		File d1 = new File(path, "d1");
		d1.mkdir();
		File d2 = new File(d1, "d2");
		d2.mkdir();
		File f3 = new File(d2, "f3.txt");
		f3.createNewFile();
		assertFalse("Folder 1 shouldn't be versioned", client.isVersioned(d1));
		assertFalse("Subfolder shouldn't be versioned, but it should be possible to ask even when parent is not versioned", client.isVersioned(d2));
		assertFalse("File in subfolder shouldn't be versioned", client.isVersioned(f3));
		f3.delete();
		d2.delete();
		d1.delete();
	}
	
	public void testMissingFileStatus() {
		File f = new File(path, "automated-test-increment.txt");
		if (!f.exists()) fail("For this test to work the file " + f.getName() + " must be under version control");
		assertTrue("Testing with an existing working copy file", client.isVersioned(f));
		assertFalse("The existing file is not modified locally", client.hasLocalChanges(f));
		// now delete without telling the svn client
		f.delete();
		assertFalse("To identify a missing file, check (exists==false && isVersioned==true)", f.exists());
		assertTrue("If the file is silently deleted, it is still versioned", client.isVersioned(f));
		try {
			assertFalse("There is nothing to check in, because the file has not been marked for removal", client.hasLocalChanges(f));
		} catch (Exception e) {
			fail("Should be able to check hasLocalChanges on a missing file, if it is isVersioned==true");
		}
	}
	
	public void testAddAndDelete() throws IOException, ConflictException, RepositoryAccessException {
		// create new file and folder (both because there is an important difference when they are deleted)
		File created = new File(path, "to be deleted.txt" + System.currentTimeMillis());
		created.createNewFile();
		File folder = new File(path, "new folder " + System.currentTimeMillis());
		folder.mkdir();
		
		assertFalse("There are no local changes until the new resources are added to version control", client.hasLocalChanges());
		
		// add the file
		client.add(created);
		assertTrue("The file should be added", client.isVersioned(created));
		assertTrue("Means that we have local changes", client.hasLocalChanges(created));
		try {
			client.delete(created);
			fail("Should say that the file can not be deleted because it has local modifications");
		} catch (ResourceHasLocalChangesException e) {
			assertEquals(created, e.getPath());
		} catch (WorkingCopyAccessException e) {
			fail("Threw the generic WorkingCopyAccessException but should have thrown specific ResourceHasLocalChangesException");
		}
		
		// add a new folder
		client.add(folder);
		assertTrue("The new folder should be added", client.isVersioned(folder));
		assertTrue("Added folder means local changes", client.hasLocalChanges(folder));
		
		// do the commit
		assertTrue("Before the commit there is added files", client.hasLocalChanges());
		client.commit("Added test file and folder");
		assertFalse("After commit we're up to date", client.hasLocalChanges());
		
		// delete both the file and the folder
		client.delete(created);
		client.delete(folder);
		assertFalse("The file should be deleted by the client", created.exists());
		assertTrue("The folder should be marked for delete but not deleted until commit", folder.exists());
		// check that the now non-existing paths both hasLocalChanges
		assertTrue("The file does not exist, but it's location is versioned", client.isVersioned(created));
		assertTrue("The file does not exist, but it's location has local changes", client.hasLocalChanges(folder));
		
		client.commit("Deleted test file and folder");
		assertFalse("The file should be gone", created.exists());
		assertFalse("The deleted folder should be gone after commit", folder.exists());
		assertFalse("Now the file name is not in use anymore", client.isVersioned(created));
		assertFalse("Now the folder name is not in use anymore", client.isVersioned(folder));
		try {
			client.hasLocalChanges(created);
			fail("hasLocalChanges(path) is undefined now that the file has been deleted, should cause exception");
		} catch (Exception e) {
			// expected
		}
	}
	
	public void testAddNonExisting() {
		File notcreated = new File(path, "does not exist " + System.currentTimeMillis());
		if (notcreated.exists()) fail("Invalid test setup. The file " + notcreated.getName() + " is already in the working copy");
		try {
			client.add(notcreated);
			fail("Add on nonexisting file or folder should cause IllegalArgumentException");
		} catch (IllegalArgumentException e) {
			// expected
		}
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

	public void testAddAlreadyAdded() throws ConflictException, RepositoryAccessException, IOException {
		File f = new File(path, "testfolder");
		f.mkdir();
		client.add(f);
		assertTrue("Folder has been added, so it is versioned", client.isVersioned(f));
		assertTrue("There is a new folder to commit", client.hasLocalChanges(f));
		try {
			client.add(f);
			fail("Should not be allowed to add() an added resource");
		} catch (WorkingCopyAccessException e) {
			// expected
		}
		
		client.commit("Added empty folder");
		assertFalse("The new folder should be versioned and committed", client.hasLocalChanges(f));
		try {
			client.add(f);
			fail("Should not be allowed to add() a versioned resource");
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
		File created = new File(path, "testDeleteFolderContainingNewFile" + System.currentTimeMillis());
		created.mkdir();
		client.add(created);
		assertTrue("The folder should have been added", client.isVersioned(created));
		client.commit("Added test folder (testDeleteFolderContainingNewFile)");
		
		File child = new File(created, "nonversioned.txt");
		child.createNewFile();
		try {
			client.delete(created);
			fail("Should have thrown WorkingCopyAccessExcption because there are local changes in the folder");
		} catch (ResourceHasLocalChangesException e) {
			assertEquals("The folder that contains the unversioned file can not be deleted, because it counts as hasLocalChanges", e.getPath());
			try {
				client.hasLocalChanges(created);
				fail("But still, it is not possible to check hasLocalChanges. Well, seems a little odd.");
			} catch (Exception e2) {
				// expected
			}
		} catch (WorkingCopyAccessException e) {
			fail("Should have thrown the more specific " + ResourceHasLocalChangesException.class.getName());
		}
		assertTrue("Should have removed the folder with the new file", client.isVersioned(created));
		
		child.delete();
		created.delete();
		client.delete(created);
		client.commit("Deleted test folder");
		assertFalse("Delete should be successful when the folder has been manually deleted", client.isVersioned(created));
	}
	
	public void testAddNew() throws IOException {
		File f1 = new File(path, "testAddNew.txt" + System.currentTimeMillis());
		f1.createNewFile();
		File d1 = new File(path, "testAddNew" + System.currentTimeMillis());
		d1.mkdir();
		File d1f = new File(d1, "file.txt");
		d1f.createNewFile();
		client.addNew(d1);
		assertTrue("The folder should have been added", client.isVersioned(d1));
		assertTrue("The folder's contents should also have been added", client.isVersioned(d1f));
		assertFalse("The file next to the folder should not have been added", client.isVersioned(f1));
		// calling addNew again should not be a problem, but it doesn't do anything
		client.addNew(d1);
		// calling addNew for a the added file should also not be a problem, but it doesn't do anything
		client.addNew(d1f);
		
		// now do addNew for the working copy
		client.addNew();
		assertFalse("Everything in the working copy should habe been added now", client.isVersioned(f1));
	}
	
}
