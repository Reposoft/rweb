/* $license_header$
 */
package se.repos.svn.config;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.SvnProxySettings;
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

	public void testSetStorePasswords() throws IOException, ConfigurationStateException {
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
		
		ClientConfiguration clientConfiguration = new RuntimeConfigurationArea(testarea);
		clientConfiguration.setStorePasswords(true);
		clientConfiguration = null;
		
		String changed1 = c.lastModified() + " " + s.lastModified();
		
		clientConfiguration = new RuntimeConfigurationArea(testarea);
		assertTrue("Client should store passwords now", clientConfiguration.isStorePasswords());
		clientConfiguration.setStorePasswords(false);
		assertFalse("Client should not store passwords now", clientConfiguration.isStorePasswords());
		
		String changed2 = c.lastModified() + " " + s.lastModified();
		assertTrue("Should have updated a config file", changed1 != changed2);
	}
	
	public void testSetProxySettings() throws ConfigurationStateException, IOException {
		SvnProxySettings settings = new SvnProxySettings("my.proxy.se", 88);
		settings.setUsername("u");
		settings.setPassword("p");
		
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
		
		RuntimeConfigurationArea config = new RuntimeConfigurationArea(testarea);
		config.setProxySettings(settings);
		config = null;
		
		// verify
		config = new RuntimeConfigurationArea(testarea);
		assertEquals(settings, config.getProxySettings());
		
		// verify file contents
		FileReader fr = new FileReader(s);
		BufferedReader reader = new BufferedReader(fr);
		String line = null;
		while ((line=reader.readLine()) != null) {
			if (line.startsWith("http-proxy-host")) break;
		}
		assertNotNull("Should have written the settings to file", line);
		
		TestFolder.cleanUp();
	}

	public void testGetConfigFolder() {
		// default config area folder -- don't know if it exists or not
		// assuming that the user running this test has used an svn client
		File area = null;
		try {
			area = RuntimeConfigurationArea.getConfigFolder();
		} catch (Exception e) {
			if (!area.exists()) fail();
			fail();
			throw new RuntimeException(); // can not happen
		}
		assertNotNull(area);
		assertTrue("The configuration area " + area + " does not exist" +
				" (thist test assumes that an svn client has set up the default area)", area.exists());
		File c = new File(area, "config");
		File s = new File(area, "servers");
		assertTrue("Should find a config file in " + area, c.exists());
		assertTrue("Should find a servers file in " + area, s.exists());
	}
	
	public void testNewConfigInNonExistingFolder() {
		File testarea = TestFolder.getNew();
		testarea.delete();
		try {
			new RuntimeConfigurationArea(testarea);
		} catch (ConfigurationStateException e) {
			fail("Should not be a problem to instantiate config in non existing folder. " +
					"Allow SVN client to create the folder before config operations are attemtped. Got: " + e);
		}
	}
	
	public void testNewConfigInEmptyFolder() {
		File testarea = TestFolder.getNew();
		try {
			new RuntimeConfigurationArea(testarea);
			fail("We accept non existing config folders, but not empty config folders (svn clients create the folder with contents)");
		} catch (ConfigurationStateException e) {
			// expected
		}
	}

}
