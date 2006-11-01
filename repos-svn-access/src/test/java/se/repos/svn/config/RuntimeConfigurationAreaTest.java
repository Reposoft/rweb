/* $license_header$
 */
package se.repos.svn.config;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.test.TestFolder;

import junit.framework.TestCase;

public class RuntimeConfigurationAreaTest extends TestCase {

	public void testGlobalIgnore() throws IOException, ConfigurationStateException {
		File testarea = TestFolder.getNew();
		assertTrue(testarea.isDirectory());
		File c = new File(testarea, "config");
		c.createNewFile();
		File s = new File(testarea, "servers");
		s.createNewFile();
		
		FileWriter fw = new FileWriter(c);
		fw.append("[miscellany]\n");
		fw.close();
		fw = new FileWriter(s);
		fw.append("[groups]\n");
		fw.append("[global]\n");
		fw.close();
		
		final String P1 = "*-mytempfiles.txt";
		final String P2 = "*.txt";
		
		ClientConfiguration clientConfiguration = new RuntimeConfigurationArea(testarea);
		clientConfiguration.addGlobalIgnore(new SvnIgnorePattern(P1));
		clientConfiguration.addGlobalIgnore(new SvnIgnorePattern(P2));
		clientConfiguration.addGlobalIgnore(new SvnIgnorePattern(P1));
		
		ClientConfiguration secondInstance = new RuntimeConfigurationArea(testarea);
		SvnIgnorePattern[] ignores = secondInstance.getGlobalIgnores();
		assertEquals("Two different ignores patterns have been added", 2, ignores.length);
		assertEquals(P1, ignores[0].getValue());
		assertEquals(P2, ignores[1].getValue());
		
		TestFolder.cleanUp();
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
