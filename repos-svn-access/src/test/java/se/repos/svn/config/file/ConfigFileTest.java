/* $license_header$
 */
package se.repos.svn.config.file;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.util.LinkedList;
import java.util.List;

import se.repos.svn.config.ConfigurationStateException;

import junit.framework.TestCase;

public class ConfigFileTest extends TestCase {

	public void testSetGlobalIgnores() throws IOException, ConfigurationStateException {
		File f = File.createTempFile("repos-svn-access", "config");
		ConfigFile config = new ConfigFile(f);
		config.setGlobalIgnores(".DS_Store");
		
		FileReader fr = new FileReader(f);
		BufferedReader file = new BufferedReader(fr);
		assertEquals("[miscellany]", file.readLine());
		assertEquals("global-ignores = .DS_Store", file.readLine());
		fr.close();
		
		config.setGlobalIgnores("TEMP Temp temp #*# .*~ *~ .#* .DS_Store");

		fr = new FileReader(f);
		file = new BufferedReader(fr);
		assertEquals("[miscellany]", file.readLine());
		assertEquals("global-ignores = TEMP Temp temp #*# .*~ *~ .#* .DS_Store", file.readLine());
		fr.close();
		
		f.delete();
	}
	
	public void testUseCommentedOutSettingsIfAvailable() throws IOException, ConfigurationStateException {
		File f = File.createTempFile("repos-svn-access", "servers");
		FileWriter fw = new FileWriter(f);
		fw.append("### My config file\n");
		fw.append("\n");
		fw.append("### Section for authentication and authorization customizations.\n");
		fw.append("[auth]\n");
		fw.append("# store-passwords = no\n");
		fw.append("# store-auth-creds = no\n");
		fw.append("\n");
		fw.append("### Section for configuring external helper applications.\n");
		fw.append("# [helpers]\n");
		fw.append(" \n");
		fw.append("### Section for configuring miscellaneous Subversion options.\n");
		fw.append("[miscellany]\n");
		fw.append("# global-ignores = *.o *.lo *.la #*# .*.rej *.rej .*~ *~ .#* .DS_Store\n");
		fw.append("\n");
		fw.append("### Section for configuring automatic properties.\n");
		fw.append("[auto-props]\n");
		fw.append("# *.txt = svn:eol-style=native\n");
		fw.append("# *.png = svn:mime-type=image/png\n");
		fw.append("\n");
		fw.close();
		
		ConfigFile config = new ConfigFile(f);
		config.setGlobalIgnores(".DS_Store");
		
		List rows = readContents(f);
		
		int g = rows.indexOf("global-ignores = .DS_Store");
		assertTrue("Should have added a global-ignores section", g>0);
		int n = rows.indexOf("[auto-props]");
		/* need to write our own ini library for this
		assertFalse("Should not add new value right before next section when there's a section description",
				n == g + 1);
		assertTrue("Should have added the new value after the last commented out example", 
				rows.get(g-1).toString().startsWith("# global-ignores"));
		*/
		f.delete();
	}

	private List readContents(File f) throws FileNotFoundException, IOException {
		FileReader fr = new FileReader(f);
		BufferedReader file = new BufferedReader(fr);
		List rows = new LinkedList();
		String line = file.readLine();
		while (line != null) {
			System.out.println(line);
			rows.add(line);
			line = file.readLine();
		}
		return rows;
	}	

}
