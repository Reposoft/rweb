/* $license_header$
 */
package se.repos.svn.config;

import java.io.File;
import java.io.IOException;

import junit.framework.TestCase;

public class RuntimeConfigurationAreaTest extends TestCase {

	public void testAddGlobalIgnore() throws IOException {
		File testarea = File.createTempFile(".subversion", "");
		testarea.mkdir();
		File c = new File(testarea, "config");
		File s = new File(testarea, "servers");
		// ...
		testarea.delete();
	}

	public void testGetGlobalIgnores() {
		
	}

	public void testGetProxySettings() {
		
	}

	public void testIsStorePasswords() {
		
	}

	public void testSetProxySettings() {
		
	}

	public void testSetStorePasswords() {
		
	}

	public void testGetConfigFolder() {
		// assuming that the user running this test has used an svn client
		File area = RuntimeConfigurationArea.getConfigFolder();
		assertNotNull(area);
		assertTrue("The configuration area " + area + " does not exist", area.exists());
		File c = new File(area, "config");
		File s = new File(area, "servers");
		assertTrue("Should find a config file in " + area, c.exists());
		assertTrue("Should find a servers file in " + area, s.exists());
	}

}
