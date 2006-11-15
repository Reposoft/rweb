/* $license_header$
 */
package se.repos.svn.config.file;

import java.util.Arrays;

import junit.framework.TestCase;

public class IniFileTest extends TestCase {

	public void testGetLineNumberForMatch() {
		String[] s = new String[] {
			"[a]",
			"# r = c",
			""
		};
		String rx = "^#\\s*r\\s*=.*$";
		assertEquals(1, new IniFile().getLineNumberForMatch(Arrays.asList(s), rx));
	}

	public void testGetLineNumberForMatchNot() {
		String[] s = new String[] {
			"[a]",
			"# r = c",
			""
		};
		String rx = "^;\\s*r\\s*=.*$";
		assertEquals(-1, new IniFile().getLineNumberForMatch(Arrays.asList(s), rx));
	}	

}
