/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

import junit.framework.TestCase;

public class ConflictExceptionTest extends TestCase {

	private final String path = "/tmp/";
	
	public void testGetMessage() {
		ConflictInformation c1 = new ConflictInformation() {
			public File getMergedFile() {return new File(path + "f.txt");}
			public File getRepositoryFile() {return new File(path + "f.txt.r10");}
			public File getTargetPath() {return getMergedFile();}
			public File getUsedRepositoryFile() {return new File(path + "f.txt.r9");}
			public File getUserFile() {return new File(path + "f.txt.mine");}
			public String toString() {
				return "c1";
			}
		};
		ConflictInformation c2 = new ConflictInformation() {
			public File getMergedFile() {return new File(path + "g.txt");}
			public File getRepositoryFile() {return new File(path + "f.txt.r1000");}
			public File getTargetPath() {return getMergedFile();}
			public File getUsedRepositoryFile() {return new File(path + "f.txt.r999");}
			public File getUserFile() {return new File(path + "f.mine");}
			public String toString() {
				return "c2";
			}
		};
		Exception e = new ConflictException(new ConflictInformation[]{c1, c2});
		String m = e.getMessage();
		assertTrue(m.contains("c1"));
		assertTrue(m.contains("c2"));
	}

}
