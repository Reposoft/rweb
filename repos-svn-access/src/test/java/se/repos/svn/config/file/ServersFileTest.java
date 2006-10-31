/* $license_header$
 */
package se.repos.svn.config.file;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.util.LinkedList;
import java.util.List;
import java.util.Properties;

import junit.framework.TestCase;
import se.repos.svn.SvnProxySettings;
import se.repos.svn.config.ConfigurationStateException;

public class ServersFileTest extends TestCase {

	public void testEmptyFileException() throws IOException {
		File f = File.createTempFile("repos-svn-access", "servers");
		try {
			new ServersFile(f);
			fail("ServersFile should throw exception in initializer if the basic sections are not in the file");
		} catch (FileNotFoundException e) {
			throw e;
		} catch (IOException e) {
			throw e;
		} catch (ConfigurationStateException e) {
			// expected
		}
		f.delete();
	}
	
	public void testAddGroup() throws IOException, ConfigurationStateException {
		File f = File.createTempFile("repos-svn-access", "servers");
		FileWriter fw = new FileWriter(f);
		fw.append("[groups]\n");
		fw.append("[global]\n");
		fw.close();
		
		ServersFile config = new ServersFile(f);
		
		// create a new group
		config.addGroup("repos", "*.repos.se");
		// add a group option
		config.setProxySettings("repos", new SvnProxySettings("1.2.3.4", 80));
		// add a global option
		config.setHttpTimeout(ServersFile.GROUP_GLOBAL, 10);
		
		FileReader fr = new FileReader(f);
		BufferedReader file = new BufferedReader(fr);
		List rows = new LinkedList();
		String line = file.readLine();
		while (line != null) {
			System.out.println(line);
			rows.add(line);
			line = file.readLine();
		}
		
		int a = rows.indexOf("[groups]");
		assertTrue("Should contain a groups section", a>=0);
		int b = rows.indexOf("repos = *.repos.se");
		assertTrue("Should contain the group definition: repos = *.repos.se", b>=0);
		int c = rows.indexOf("[repos]");
		assertTrue("Should contain the added group as a section name", c>=0);
		int d = rows.indexOf("http-proxy-host = 1.2.3.4");
		assertTrue("Should contain a http-proxy-host setting", d>=0);
		
		assertTrue("The group definition should be under the [groups] section", a<b);
		assertTrue("The added group should be below the [groups] section", b<c);
		assertTrue("Should contain a http-proxy-host setting for the group [repos]", c<d);
		
		int k = rows.indexOf("[global]");
		int m = rows.indexOf("http-timeout = 10");
		assertTrue("Should contain a [global] section", k>=0);
		assertTrue("Should contain a http-proxy-port settting", m>=0);
		assertTrue("http-timeout should be a global setting", m>k);
		
		f.delete();
	}

	public void testSetProxyUsernameAndPassword() throws IOException, ConfigurationStateException {
		File f = File.createTempFile("repos-svn-access", "servers");
		FileWriter fw = new FileWriter(f);
		fw.append("[groups]\n");
		fw.append("[global]\n");
		fw.close();
		
		SvnProxySettings proxySettings = new SvnProxySettings("1.2.3.4", 1234);
		proxySettings.setUsername("svensson");
		proxySettings.setPassword("medel");
		
		ServersFile config = new ServersFile(f);
		config.setProxySettings(ServersFile.GROUP_GLOBAL, proxySettings);
		
		Properties p = new Properties();
		p.load(new FileInputStream(f));
		
		assertTrue(p.containsKey("http-proxy-host"));
		assertEquals("1.2.3.4", p.get("http-proxy-host"));
		assertTrue(p.containsKey("http-proxy-port"));
		assertEquals("1234", p.get("http-proxy-port"));
		assertTrue(p.containsKey("http-proxy-username"));
		assertEquals("svensson", p.get("http-proxy-username"));
		assertTrue(p.containsKey("http-proxy-password"));
		assertEquals("medel", p.get("http-proxy-password"));
		
		f.delete();
	}

}
