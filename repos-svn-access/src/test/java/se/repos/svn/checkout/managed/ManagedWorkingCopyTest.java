/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.test.CheckoutSettingsForTest;
import junit.framework.TestCase;

public class ManagedWorkingCopyTest extends TestCase {

	public void testPathDeleteRecursive() {
		// this is a helper function
		CheckoutSettings settings = new CheckoutSettingsForTest();
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings);
		File d = new File(settings.getWorkingCopyFolder(), "will be deleted very soon");
		d.mkdir();
		c.pathDeleteRecursive(d);
		assertFalse("Folder should have been deleted", d.exists());
		// and it could be a dangerous helper function
		File outside = new File(".");
		try {
			c.pathDeleteRecursive(outside);
			fail("Delete recursive must throw exception on paths outside the working copy");
		} catch (IllegalArgumentException e) {
			// expected
		}
	}
	
}
