/* $license_header$
 */
package se.repos.svn.checkout.commontests;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;

import junit.framework.TestCase;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.ResourceHasLocalChangesException;
import se.repos.svn.checkout.ResourceNotVersionedException;
import se.repos.svn.checkout.ResourceParentNotVersionedException;
import se.repos.svn.checkout.WorkingCopyAccessException;
import se.repos.svn.test.CheckoutSettingsForTest;
import se.repos.svn.test.TestNotifyListener;

/**
 * Tests svn add, delete and status.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo when there is more than one working copy implementation, make this abstract and use to test all implementations
 */
public class StatusAddDeleteIntegrationTest extends TestCase {

	// test workingcopy path
	private File path;
	
	private ReposWorkingCopy client;
	
	TestNotifyListener testNotifyListener = null;
	
	public void setUp() throws Exception {
		super.setUp();
		CheckoutSettings settings = new CheckoutSettingsForTest();
		path = settings.getWorkingCopyFolder();
		client = AllTests.getClient(settings, getName());
		testNotifyListener = new TestNotifyListener();
		client.addNotifyListener(testNotifyListener);
		client.checkout();
	}
	
	public void tearDown() {
		path = null;
		client = null;
		CheckoutSettingsForTest.tearDown();
	}
	
	private void assertSameFile(String message, File expected, File actual) throws IOException {
		System.out.println("Comparing " + expected + " and " + actual + (actual.isAbsolute() ? " (absolute)" : " (relative)"));
		if (!actual.isAbsolute()) {
			assertTrue(message, expected.toString().endsWith(actual.toString()));
			return;
		}
		// in windows there is sometimes a difference in >8 char foldernames
		assertEquals(message, expected.getCanonicalPath(), actual.getCanonicalPath());
	}
	
	public void testWorkingCopyRootFolderStatus() throws IOException {
		assertTrue("Working copy root folder should of course be versioned", client.isVersioned(path));
		assertFalse("After checkout working copy has no changes", client.hasLocalChanges());
		assertFalse("After checkout working copy root folder has no changes", client.hasLocalChanges(path));
	}
	
	public void testFileOutsideWorkingCopy() throws IOException {
		File tmp = File.createTempFile("testFileOutsideWorkingCopy", "file");
		tmp.deleteOnExit();
		try {
			client.isVersioned(tmp);
			fail("Should have reported error because the parent folder is not versioned");
		} catch (ResourceNotVersionedException e) {
			assertEquals("The exception should report the non-versioned path of the parent folder",
					tmp.getParentFile().getCanonicalPath(), e.getPath().getCanonicalPath());
		}
		tmp.delete();
		assertEquals("The isVersioned error does not give an error notify", 0, testNotifyListener.errors.size());
	}
	
	public void testNewFileStatus() throws IOException {
		File created = new File(path, getName() + ".txt");
		if (created.exists()) fail("Invalid test setup. The file " + created.getName() + " is already in the working copy");
		assertFalse("A file that does not exist and is not under version control is not versioned", client.isVersioned(created));
		try {
			// note that the file does not exist yet
			client.hasLocalChanges(created);
			fail("Should be invalid to ask for status on a path that does not exist AND is not versioned (check with isVersioned first)");
		} catch (ResourceNotVersionedException e) { 
			assertEquals("The exception should state the invalid path", created.getCanonicalPath(), e.getPath().getCanonicalPath());
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
		assertEquals("svn status can be checked, so there's no error notify", 0, testNotifyListener.errors.size());
	}
	
	public void testNewFolderStatus() throws IOException {
		File d1 = new File(path, "d1");
		d1.mkdir();
		File d2 = new File(d1, "d2");
		d2.mkdir();
		File f3 = new File(d2, "f3.txt");
		f3.createNewFile();
		assertFalse("Folder 1 shouldn't be versioned", client.isVersioned(d1));
		try {
			assertFalse("Subfolder shouldn't be versioned, and parent is not", client.isVersioned(d2));
			assertFalse("File in subfolder shouldn't be versioned", client.isVersioned(f3));
			fail("isVersioned is defined to throw exception if parent path is not versioned");
		} catch (ResourceParentNotVersionedException e) {
			assertEquals("Parent is not a working copy folder", d1.getCanonicalPath(), e.getPath().getCanonicalPath());
		}
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
		File created = new File(path, "testAddAndDelete file.txt" + System.currentTimeMillis());
		created.createNewFile();
		File folder = new File(path, "testAddAndDelete folder " + System.currentTimeMillis());
		folder.mkdir();
		
		// add the folder and the file
		client.add(created);
		assertTrue("The file should be added", client.isVersioned(created));
		assertTrue("Means that we have local changes", client.hasLocalChanges(created));
		try {
			client.delete(created);
			fail("Should say that the file can not be deleted because it has local modifications");
		} catch (ResourceHasLocalChangesException e) {
			this.assertSameFile("Should report the invalid add", created, e.getPath());
		} catch (WorkingCopyAccessException e) {
			fail("Threw the generic WorkingCopyAccessException but should have thrown specific ResourceHasLocalChangesException");
		}
		
		// add a new folder
		client.add(folder);
		assertTrue("The new folder should be added", client.isVersioned(folder));
		assertTrue("Added folder means local changes", client.hasLocalChanges(folder));
		
		// do the commit
		assertTrue("Before the commit there is added files", client.hasLocalChanges());
		client.commit(path, "Added test file and folder");
		assertFalse("After commit we're up to date", client.hasLocalChanges());
		
		// delete both the file and the folder
		client.delete(created);
		client.delete(folder);
		assertFalse("The file should be deleted by the client", created.exists());
		assertTrue("The folder should be marked for delete but not deleted until commit", folder.exists());
		// check that the now non-existing paths both hasLocalChanges
		assertTrue("The file does not exist, but it's location is versioned", client.isVersioned(created));
		assertTrue("The file does not exist, but it's location has local changes", client.hasLocalChanges(folder));
		
		client.commit(path, "Deleted test file and folder");
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
		File f = new File(path, getName() + System.currentTimeMillis());
		f.mkdir();
		client.add(f);
		assertTrue("Folder has been added, so it is versioned", client.isVersioned(f));
		assertTrue("There is a new folder to commit", client.hasLocalChanges());
		client.commit(path, "Added empty folder");
		assertFalse("Folder committed already", client.hasLocalChanges());
		client.delete(f);
		assertTrue("There is a delete operation to commit", client.hasLocalChanges());
		client.commit(path, "Deleted empty folder");
		assertFalse("Client should have deleted the folder on commit (or directly when marked for deletion?)", f.exists());
		assertFalse("Working copy is up to date", client.hasLocalChanges());
	}	

	public void testAddNotRecursive() throws ConflictException, RepositoryAccessException, IOException {
		File d = new File(path, getName() + System.currentTimeMillis());
		d.mkdir();
		File f = new File(d, "child.txt");
		f.createNewFile();
		File f2 = new File(d, "child2.txt");
		f2.createNewFile();
		
		// first try to add the file before the folder is added
		try {
			client.add(f);
			fail("Should have thrown exception that the parent folder is not versioned");
		} catch (ResourceParentNotVersionedException e) {
			assertEquals("non-versoined parent", d.getCanonicalPath(), e.getPath().getCanonicalPath());
		}
		
		// add the folder non-recursively
		client.add(d);
		assertTrue("Folder has been added, so it is versioned", client.isVersioned(d));
		assertFalse("add(file) is not recursive, so file should not be versioned", client.isVersioned(f));
		assertTrue("The added folder is local changes", client.hasLocalChanges(d));
		try {
			client.add(d);
			fail("Should not be allowed to add() an added resource, because it would not add the contents");
		} catch (WorkingCopyAccessException e) {
			// expected
		}
		
		// add the file too
		client.add(f);
		client.commit(path, getName());
		assertFalse("The new folder should be versioned and committed", client.hasLocalChanges(d));
		
		// clean up
		f2.delete();
		client.delete(d);
		client.commit(path, getName() + " clean");
	}	
	
	public void testDeleteAlreadyDeletedFile() throws IOException, ConflictException, RepositoryAccessException {
		File created = new File(path, "testDeleteAlreadyDeletedFile" + System.currentTimeMillis());
		created.createNewFile();
		client.add(created);
		assertTrue("The file should be added", client.isVersioned(created));
		client.commit(path, "Added test file (testDeleteAlreadyDeletedFile)");
		created.delete(); // remove the file in file system
		try {
			client.delete(created);
		} catch (IllegalArgumentException e) {
			fail("All svn clients should be able to mark a missing file for deletion.");
		}
		assertTrue("Now the file has local changes", client.hasLocalChanges(created));
		assertFalse("The file should be gone from the file system immediately after svn delete", created.exists());
		client.commit(path, "Delete test done");
	}
	
	public void testDeleteFolderContainingNewFile() throws IOException, ConflictException, RepositoryAccessException {
		File created = new File(path, getName() + System.currentTimeMillis());
		created.mkdir();
		client.add(created);
		assertTrue("The folder should have been added", client.isVersioned(created));
		client.commit(path, getName() + " setup");
		
		File child = new File(created, "nonversioned.txt");
		child.createNewFile();
		try {
			client.delete(created);
			fail("Should have thrown WorkingCopyAccessExcption because there are local changes in the folder");
		} catch (ResourceNotVersionedException e) {
			this.assertSameFile("The exception should be thrown for the modified file, not the folder", child, e.getPath());
		} catch (WorkingCopyAccessException e) {
			fail("Should have thrown the more specific " + ResourceHasLocalChangesException.class.getName());
		}
		assertTrue("Folders should never be deleted when marked for delete", created.exists());
		assertTrue("The contents of the folder should also still be there", child.exists());
		assertFalse("Status of the folder should not have changed", client.hasLocalChanges(created));
		
		// simply delete the folder maually before marking for delete to work around this restriction
		child.delete();
		created.delete();
		client.delete(created);
		assertTrue("The folder should be versioned until commit", client.isVersioned(created));
		client.commit(path, "Deleted test folder");
		assertFalse("Delete should be successful when the folder has been manually deleted", client.isVersioned(created));
		assertEquals("Should be reported to the notify listeners", 1, testNotifyListener.errors.size());
	}
	
	public void testDeleteFolderContainingModifiedFile() throws IOException, ConflictException, RepositoryAccessException {
		File d = new File(path, getName() + System.currentTimeMillis());
		d.mkdir();
		File f1 = new File(d, "versioned.txt");
		f1.createNewFile();
		client.add(d);
		client.add(f1);
		assertTrue("The folder and file should have been added", client.isVersioned(f1));
		client.commit(path, getName() + " setup");
		
		// modify the file
		new FileWriter(f1).append('a').close();
		assertTrue("New contents in the file, has local modifications", client.hasLocalChanges(f1));
		
		try {
			client.delete(d);
			fail("Should have thrown WorkingCopyAccessExcption because there are local changes in the folder");
		} catch (ResourceHasLocalChangesException e) {
			this.assertSameFile("The exception should be thrown for the modified file, not the folder", f1, e.getPath());
			assertTrue("The folder has local changes because of the modified file", client.hasLocalChanges(d));
		} catch (WorkingCopyAccessException e) {
			fail("Should have thrown the more specific " + ResourceHasLocalChangesException.class.getName());
		}
		assertTrue("Should have removed the folder with the new file", client.isVersioned(d));
		
		// revert all changes and then delete
		client.revert(f1);
		client.delete(d);
		client.commit(path, getName() + " clean");
		assertFalse("Delete should be successful when the changes have been reverted", client.isVersioned(d));
		assertEquals("Should be reported to the notify listeners", 1, testNotifyListener.errors.size());
	}
	
	// addNew is further tested with ignore patterns in properties test case
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
		
		// now do addNew for the working copy, which should include the file too
		client.addNew(path);
		assertTrue("Everything in the working copy should have been added now", client.isVersioned(f1));
	}
	
	public void testAddTreeManually() throws IOException {
		// create a file tree which we will recurse manually
		File d1 = new File(path, "testAddTreeManually" + System.currentTimeMillis());
		d1.mkdir();
		File f1 = new File(d1, "new.txt");
		f1.createNewFile();
		
		// manually check ignores
		assertFalse("This folder does not match any ignore pattern", client.isIgnore(d1));
		try {
			client.isIgnore(f1);
			fail("Should have thrown exception on isIgnore inside folder that is not added yet");
		} catch (Exception e) {
			// expected
		}
		
		// add the folder
		client.add(d1);
		assertTrue("Added folder should be versioned", client.isVersioned(d1));
		// add file after ignore check
		assertFalse("File is in versioned folder, should not be ignored", client.isIgnore(f1));
		client.add(f1);
		assertTrue("Added file in added folder should be versioned", client.isVersioned(f1));
		
		assertFalse("API says: For folders that ... are in version control, [ignore] returns false", client.isIgnore(d1));
	}
	
}
