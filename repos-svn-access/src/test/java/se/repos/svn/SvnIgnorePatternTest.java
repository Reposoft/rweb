/* $license_header$
 */
package se.repos.svn;

import java.io.File;
import java.util.Arrays;
import java.util.Collection;
import java.util.HashSet;
import java.util.List;

import junit.framework.TestCase;

public class SvnIgnorePatternTest extends TestCase {

	public void testSvnIgnorePatternFile() {
		File f = new File("/folder/file.txt");
		SvnIgnorePattern p = new SvnIgnorePattern(f);
		assertEquals("The ignore pattern should be the file name", "file.txt", p.getValue());
		assertEquals("The toString method should return the value", "file.txt", p.toString());
	}
	
	public void testEquals() {
		SvnIgnorePattern p = new SvnIgnorePattern("temp");
		SvnIgnorePattern s = new SvnIgnorePattern("temp");
		assertTrue("The equals method should compare the values", p.equals(s));
		//Collection c = new LinkedList();//new HashSet();
		Collection c = new HashSet(); // requires hashCode too
		c.add(p);
		assertTrue("Equal means that collection.contains works", c.contains(s));
	}
	
	public void testEqualsFile() {
		File f = new File("/folder/file.txt");
		SvnIgnorePattern p = new SvnIgnorePattern(f);
		
		SvnIgnorePattern s = new SvnIgnorePattern("file.txt");
		
		assertTrue("The equals method should compare the values", p.equals(s));
	}
	
	public void testConvertStringListToArray() {
		List list = Arrays.asList(new String[]{ "*.txt", "temp" });
		SvnIgnorePattern[] ignores = SvnIgnorePattern.array(list);
		assertEquals("All entries should be wrapped as SvnIgnorePattern", list.size(), ignores.length);
		assertTrue("Should contain the first entry", Arrays.asList(ignores).contains(new SvnIgnorePattern("*.txt")));
		assertTrue("Should contain the second entry", Arrays.asList(ignores).contains(new SvnIgnorePattern("temp")));
	}
	
}