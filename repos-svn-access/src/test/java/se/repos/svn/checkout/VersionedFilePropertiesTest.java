/* $license_header$
 */
package se.repos.svn.checkout;

import junit.framework.TestCase;

public class VersionedFilePropertiesTest extends TestCase {

	public void testValidateMimeType() {
		new VersionedFileProperties.MimeType("a/b");
		new VersionedFileProperties.MimeType("text/plain");
		try {
			new VersionedFileProperties.MimeType(null);
			fail("should be invalid");
		} catch (IllegalArgumentException e) {}
		try {
			new VersionedFileProperties.MimeType("");
			fail("should be invalid");
		} catch (IllegalArgumentException e) {}
		try {
			new VersionedFileProperties.MimeType("nothing");
			fail("should be invalid");
		} catch (IllegalArgumentException e) {}
		try {
			new VersionedFileProperties.MimeType("/plain");
			fail("should be invalid");
		} catch (IllegalArgumentException e) {}
	}

}
