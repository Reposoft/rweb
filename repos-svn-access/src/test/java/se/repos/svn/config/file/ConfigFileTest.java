/* $license_header$
 */
package se.repos.svn.config.file;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;

import junit.framework.TestCase;

public class ConfigFileTest extends TestCase {

	public void testSetGlobalIgnores() throws IOException {
		File f = File.createTempFile("repos-svn-access", "config");
		ConfigFile config = new ConfigFile(f);
		config.setGlobalIgnores("TEMP Temp temp #*# .*~ *~ .#* .DS_Store");
		
		FileReader fr = new FileReader(f);
		BufferedReader file = new BufferedReader(fr);
		assertEquals("[miscellany]", file.readLine());
		assertEquals("global-ignores = TEMP Temp temp #*# .*~ *~ .#* .DS_Store", file.readLine());
		
		f.delete();
	}
	
	

}
