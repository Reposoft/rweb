/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.util.Arrays;
import java.util.List;

import se.repos.svn.SvnIgnorePattern;
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
		System.out.println("---------- " + super.getName() + " ----------");
		CheckoutSettings settings = new CheckoutSettingsForTest();
		path = settings.getWorkingCopyFolder();
		client = init(settings);
	}
	
	public void tearDown() {
		path = null;
		client = null;
		CheckoutSettingsForTest.tearDown();
	}
	
	protected ManagedWorkingCopy init(CheckoutSettings settings) throws RepositoryAccessException {
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings);
		c.checkout(); // ManagedWorkingCopy needs explicit checkout
		return c;
	}
	
	public void testDefaultClientConfiguration() {
		// TODO remove a global ignore and it should be recovered
		List defaultIgnores = Arrays.asList(client.getClientSettings().getGlobalIgnores());
		assertTrue(defaultIgnores.contains(new SvnIgnorePattern("temp")));
	}
	
	public void testDeleteAlreadyDeletedFile() throws IOException, ConflictException, RepositoryAccessException {
		File created = new File(path, "tobedeleted.txt" + System.currentTimeMillis());
		created.createNewFile();
		BufferedWriter out = new BufferedWriter(new FileWriter(created));
        out.write("someContents"); // empty file is too easy to revert
        out.close();
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

	public void testDeleteAlreadyDeletedFolder() throws IOException, ConflictException, RepositoryAccessException {
		File created = new File(path, "tobedeleted" + System.currentTimeMillis());
		created.mkdir();
		File createdFile = new File(created, "file.txt");
		createdFile.createNewFile();
		BufferedWriter out = new BufferedWriter(new FileWriter(createdFile));
        out.write("someContents"); // empty file is too easy to revert
        out.close();
		client.addNew(created);
		assertTrue("The folder should be added", client.isVersioned(created));
		assertTrue("The file in the folder should be added with the folder", client.isVersioned(createdFile));
		client.commit("Added test folder (testDeleteAlreadyDeletedFolder)");
		
		// delete recursively in filesystem
		createdFile.delete();
		created.delete();
		// now try to delete with svn
		client.delete(created);
		assertTrue("Should mark the folder for deletion, even if it is gone already, " +
				"and report that the path hasLocalChanges", client.hasLocalChanges(created));
		assertTrue("Also the file inside the deleted folder should be marked for deletion", 
				client.hasLocalChanges(createdFile));
		// the folder may still exist, if it was there before the delete, but the file should be gone
		assertFalse("The deleted file should be gone", createdFile.exists());
		// commit the delete
		client.commit("Deleted test folder (testDeleteAlreadyDeletedFolder)");
		assertFalse("After commit the folder should definitely be gone", created.exists());
		assertFalse("Now the folder name is not in use anymore", client.isVersioned(created));
	}	
	
	public void testMoveAlreadyMovedFolder() throws IOException, ConflictException, RepositoryAccessException {
		File f = new File(path, "tobemoved" + System.currentTimeMillis());
		File d = new File(path, "destination" + System.currentTimeMillis());
		if (f.exists()) fail("Test setup error. Folder exists " + f);
		if (d.exists()) fail("Test setup error. Folder exists " + d);
		f.mkdir();
		if (!f.exists() || !f.isDirectory()) fail("Test setup error. Should have created folder " + f);
		// add some contents so it's not too easy
		File ff = new File(f, "afile.txt");
		ff.createNewFile();
		BufferedWriter out = new BufferedWriter(new FileWriter(ff));
        out.write("someContents");
        out.close();
		// add to repository
		client.add(f);
		client.commit("test move already moved");
		if (!client.isVersioned(f)) fail("Test error. Seems the folder " + f + " was not added");
		if (client.hasLocalChanges(f)) fail("Test error. The folder " + f + " has not been committed");
		
		f.renameTo(d);
		if (f.exists()) fail("Test setup error. Folder should be gone " + f);
		assertTrue("Double check: Folder is still versioned even if it is removed", client.isVersioned(f));
		//try {
			client.move(f, d);
		//	fail("When moving a versioned directory, the .svn folder stays ant the SVN client can not recover. Should throw IllegalArgumentException.");
		//} catch (IllegalArgumentException e) {
		//	assertTrue("Error message should say that the destination is versioned", 
		//			e.getMessage().contains("destination") && e.getMessage().contains("versioned"));
		//	return; // test done
		//}
		//// if ManagedWorkingClient implements support for automatically handling moved folders, the below should pass
		assertTrue("The original folder is gone, so it has local changes", client.hasLocalChanges(f));
		assertTrue("The destination folder should exist", d.exists());
		assertTrue("The destination folder has local changes", client.hasLocalChanges(d));
		client.commit("test move already moved done");
		assertFalse("The original folder should be gone", f.exists());
		assertFalse("After commit, there is no trace of original folder", client.hasLocalChanges(f));
		assertFalse("After commit, destination file is up to date", client.hasLocalChanges(d));
		client.delete(d);
		client.commit("test move already moved cleaned");
		if (d.exists()) fail("Test error. Could not remove destination folder after test.");
	}
	
	public void testAddRecursiveWithDefaultIgnores() throws IOException, ConflictException, RepositoryAccessException {
		File f = new File(path, "tobeadded" + System.currentTimeMillis());
		f.mkdir();
		File folder = new File(f, "newfolder");
		folder.mkdir();
		File folderFile = new File(folder, "file.txt");
		folderFile.createNewFile();
		// Repos naming convention: "temp" should have global ignore
		File tempFolder = new File(f, "temp");
		tempFolder.mkdir();
		// Recommended (but not default) subversion global ignores, should be enabled in repos
		File tempFile = new File(f, "#file.txt#");
		tempFile.mkdir();
		// now the folder should be added recursively
		client.addNew(f);
		// verify
		assertTrue("'add' should have been performed", client.isVersioned(f));
		assertTrue("addNew should be recursive", client.isVersioned(folder));
		assertTrue("Recursive two steps", client.isVersioned(folderFile));
		assertFalse("Should ignore folders named 'temp'", client.isVersioned(tempFolder));
		assertFalse("Subversion recommends ignoring #*#", client.isVersioned(tempFile));
		client.commit("Recusive add but with global ignores");
		// now explicitly add the ignored
		client.add(tempFolder);
		client.add(tempFile);
		assertTrue("Folders ignored by default can be added explicitly", client.isVersioned(tempFolder));
		assertTrue("Files ignored by default can be added explicitly", client.isVersioned(tempFile));
		client.commit("Explicit add needed for names matching the global ignores pattern");
	}
	
}
