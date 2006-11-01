/* $license_header$
 */
package se.repos.svn.checkout.commontests;

import java.io.File;
import java.io.IOException;

import junit.framework.TestCase;
import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.VersionedProperty;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.ReposWorkingCopyFactory;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.WorkingCopyAccessException;
import se.repos.svn.test.CheckoutSettingsForTest;

/**
 * The logic around properties deserves a separate test case
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ReposWorkingCopyPropertiesIntegrationTest extends TestCase {

	// test workingcopy path
	private File path;
	
	private ReposWorkingCopy client;
	
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
	
	protected ReposWorkingCopy init(CheckoutSettings settings) throws RepositoryAccessException {
		ReposWorkingCopy c = ReposWorkingCopyFactory.getClient(settings);
		c.checkout();
		return c;
	}
	
	public void testProperties() throws ConflictException, RepositoryAccessException {
		File f = new File(path, "folder" + System.currentTimeMillis());
		f.mkdir();
		client.add(f);
		
		// for supported properties, the application should provide a VersionedProperty implementation
		// that wraps and validates the value
		client.getProperties(f).setProperty(new VersionedProperty() {
			public String getName() {
				return "repos:test";
			}
			public String getValue() {
				return "1.X";
			}
		});
		
		assertEquals("1.X", client.getProperties(f).getProperty("repos:test").getValue());
		client.commit("Added a custom versioned property");
		assertFalse("Everything should be committed", client.hasLocalChanges(f));
		
		client.getProperties(f).setProperty(new VersionedProperty() {
			public String getName() {
				return "repos:test";
			}
			public String getValue() {
				return "2.Y";
			}
		});
		
		assertTrue("Property change counts as local modification", client.hasLocalChanges(f));
		assertEquals("2.Y", client.getProperties(f).getProperty("repos:test").getValue());
		
		try {
			client.delete(f);
			fail("Should not be able to delete folder because it has local property modifications");
		} catch (WorkingCopyAccessException e) {
			// expected
		}
		client.revert(f);
		assertEquals("1.X", client.getProperties(f).getProperty("repos:test").getValue());
		client.delete(f);
		client.commit("Cleaned up after property test");
	}
	
	public void testIsIgnoreNotVersionedParent() {
		File f = new File(path, "newfolder" + System.currentTimeMillis());
		f.mkdir();
		File child = new File(f, "child");
		child.mkdir();
		try {
			client.isIgnore(child);
			fail("Should throw exception because the parent folder is not versioned");
		} catch (IllegalArgumentException e) {
			// expected
		}
	}
	
	public void testIsIgnoreVersioned() {
		File f = new File(path, "newfolder" + System.currentTimeMillis());
		f.mkdir();
		client.add(f);
		
		File child = new File(f, "child");
		child.mkdir();
		client.add(child);
		assertFalse("Should report NOT ignored because the file IS under version control", 
				client.isIgnore(child));
	}	
	
	public void testIsIgnoreAdministrativeAreaFolder() throws Exception {
		File svn = new File(path, ".svn");
		assertFalse("The administrative area should definitely not be versioned", client.isVersioned(svn));
		if (!svn.exists()) throw new Exception("Can not find administrative area '.svn' in working copy, so this test can not continue.");
		assertTrue("The administrative area should always be ignored", client.isIgnore(svn));
	}
	
	public void testIgnoreProperty() throws ConflictException, RepositoryAccessException, IOException {
		assertFalse(client.hasLocalChanges());
		File parent = new File(path, "newfolder" + System.currentTimeMillis());
		parent.mkdir();
		client.add(parent);
		client.commit("Added empty folder for ignore test"); // not needed, can set properties for newly added path too
		
		File child = new File(parent, "file.txt");
		child.createNewFile();
		assertFalse("The name file.txt should not be ignored, it has no matching ignore pattern", client.isIgnore(child));
		
		// set the ignore property
		client.getPropertiesForFolder(parent).setIgnore(new SvnIgnorePattern(child));
		assertTrue("The new property at the parent should count as a local change", 
				client.hasLocalChanges(parent));
		assertFalse("The new file has a matching ignore property, but it is not added so it has no local changes",
				client.hasLocalChanges(child));
		assertFalse("The file can not be versioned, because then ignore would not be reported",
				client.isVersioned(child));
		assertTrue("The local svn:ignore property should now contain 'file.txt'",
				client.getProperties(parent).getProperty("svn:ignore").getValue().contains("file.txt"));
		assertTrue("'file.txt' should now be in the ignores list",
				client.getPropertiesForFolder(parent).getIgnores().length > 0);
		assertTrue("file.txt should be ignored, it has a matching ignore pattern",
				client.isIgnore(child));
		
		// now check result after the ignored resource has been explicitly added
		client.add(child);
		assertFalse("Added resources are not 'ignore' even if they match", client.isIgnore(child));
		
		// check the property value directly
		VersionedProperty prop = client.getProperties(parent).getProperty("svn:ignore");
		assertEquals("Property svn:ignore is standard", "svn:ignore", prop.getName());
		assertEquals("Value of svn:ignore should be 'file.txt'", "file.txt", prop.getValue().trim());
		
		// clean up
		client.revert(child); // not really needed, parent revert is recursive
		child.delete(); // needed because folder with local changes can not be marked for deleteion
		client.revert(parent); // revert the propset
		client.delete(parent);
		client.commit("Cleaned up after ignore test");
	}
	
	public void testIsIgnoreNonExisting() throws ConflictException, RepositoryAccessException {
		assertFalse(client.hasLocalChanges());
		File f = new File(path, "newfolder" + System.currentTimeMillis());
		f.mkdir();
		client.add(f);
		
		File child = new File(f, "file.txt");
		try {
			client.isIgnore(child);
			fail("Should throw IllegalArgumentException for isIgnore on path that does not exist.");
		} catch (IllegalArgumentException e) {
			// expected
		}
		
		try {
			client.getPropertiesForFolder(f).setIgnore(new SvnIgnorePattern(child));
		} catch (RuntimeException e) {
			fail("Should allow new ignore value that is the name of a file even if the file does not exist");
		}
	}
	
	public void testGlobalIgnore() {
		// crrently this library can only add global ignores, not remove them
		// every client should respect global ignores
		// but we don't know what patterns exist on the test machine, so it can not be tested here
		// check ManagedWorkingCopy
	}	
	
}
