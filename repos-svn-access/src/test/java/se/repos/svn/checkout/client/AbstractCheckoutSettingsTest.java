/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.io.IOException;

import junit.framework.TestCase;

import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.test.TestFolder;

public class AbstractCheckoutSettingsTest extends TestCase {

	final String S = File.separator;
	
	public void setUp() throws Exception {
	}
	
	public void testInitialize() throws IllegalArgumentException, IOException {
		File path = TestFolder.getNew();
		CheckoutSettings c = new AbstractCheckoutSettings("http://test.repos.se/testrepo", path) {
			public UserCredentials getLogin() {
				return null;
			}
		};
		assertEquals("http://test.repos.se/testrepo", c.getCheckoutUrl().toString());
		assertEquals(path.getPath(), c.getWorkingCopyFolder().getPath());
	}

	public void testInitializeInvalidUrl() {
		try {
			new AbstractCheckoutSettings("test.repos.se/testrepo", S + "tmp") {
				public UserCredentials getLogin() {
					return null;
				}
			};
			fail("Should have thrown exception on invalid url");
		} catch (IllegalArgumentException e) {
			// expected
		} catch (RuntimeException e) {
			fail("Expected IllegalArgumentException");
		}
	}	

	public void testInitializeRelativePath() {
		try {
			new AbstractCheckoutSettings("http://test.repos.se/testrepo", "tmp") {
				public UserCredentials getLogin() {
					return null;
				}
			};
			fail("Should have thrown exception on relative path");
		} catch (IllegalArgumentException e) {
			// expected
		} catch (RuntimeException e) {
			fail("Expected IllegalArgumentException");
		}
	}	
	
	public void testToRelative() {
		File testDir = TestFolder.getNew();
		CheckoutSettings c = new AbstractCheckoutSettings("http://test.repos.se/testrepo", testDir) {
			public UserCredentials getLogin() {
				return null;
			}
		};
		File f = new File(testDir + S + "file.txt");
		String rel = c.toRelative(f);
		assertEquals("Should strip the working copy directory from the absolute path", "file.txt", rel);
	}
	
	public void testValidateWorkingCopyPath() {
		File testDir = TestFolder.getNew();
		AbstractCheckoutSettings c = new AbstractCheckoutSettings("http://test.repos.se/testrepo", testDir) {
			public UserCredentials getLogin() {return null;}
		};
		try {
			c.validateWorkingCopyPath(testDir);
		} catch (IllegalArgumentException e) {
			fail("An existing empty folder is a valid working copy path");
		}
	}

	public void testValidateWorkingCopyPathNonEmpty() throws IOException {
		File testDir = TestFolder.getNew();
		File file = new File(testDir, "a.txt");
		file.createNewFile();
		file.deleteOnExit();
		AbstractCheckoutSettings c = new AbstractCheckoutSettings("http://test.repos.se/testrepo", testDir) {
			public UserCredentials getLogin() {return null;}
		};
		try {
			c.validateWorkingCopyPath(file);
			fail(file + " is a file so it is not a valid working copy");
		} catch (IllegalArgumentException e) {
			// expected
		}
	}	
	
}
