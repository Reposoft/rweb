/* $license_header$
 */
package se.repos.svn.checkout.client;


import java.io.File;

import junit.framework.TestCase;

import org.junit.Before;

import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.CheckoutSettingsForTest;

public class AbstractCheckoutSettingsTest extends TestCase {

	final String S = File.separator;
	
	@Before
	public void setUp() throws Exception {
	}
	
	public void testInitialize() {
		CheckoutSettings c = new AbstractCheckoutSettings("http://test.repos.se/testrepo", S + "tmp") {
			public UserCredentials getLogin() {
				return null;
			}
		};
		assertEquals("http://test.repos.se/testrepo", c.getCheckoutUrl().toString());
		assertEquals(S + "tmp", c.getWorkingCopyDirectory().getPath());
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
		CheckoutSettings c = new CheckoutSettingsForTest();
		final String testDir = c.getWorkingCopyDirectory().getAbsolutePath();
		String rel = c.toRelative(new File(testDir + S + "file.txt"));
		assertEquals("Should strip the working copy directory from the absolute path", "file.txt", rel);
	}

}
