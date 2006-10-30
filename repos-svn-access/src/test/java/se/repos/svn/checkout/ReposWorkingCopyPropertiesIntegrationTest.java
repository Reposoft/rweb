/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;
import java.io.IOException;

import se.repos.svn.SvnIgnorePattern;

import junit.framework.TestCase;

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
	
	public void testIgnoreProperty() throws ConflictException, RepositoryAccessException, IOException {
		assertFalse(client.hasLocalChanges());
		File f = new File(path, "newfolder" + System.currentTimeMillis());
		f.mkdir();
		client.add(f);
		client.commit("Added empty folder for ignore test");
		
		File child = new File(path, "file.txt");
		child.createNewFile();
		assertFalse(client.isIgnore(child));
		client.getPropertiesForFolder(f).setIgnore(new SvnIgnorePattern(child));
		assertTrue(client.isIgnore(child));
		// now check result after the ignored resource has been explicitly added
		client.add(child);
		assertFalse("Added resources are not 'ignore' even if they match", client.isIgnore(child));
	}
	
	public void testGlobalIgnore() {
		// crrently this library can only add global ignores, not remove them
		// every client should respect global ignores
		// but we don't know what patterns exist on the test machine, so it can not be tested here
		// check ManagedWorkingCopy
	}	
	
}
