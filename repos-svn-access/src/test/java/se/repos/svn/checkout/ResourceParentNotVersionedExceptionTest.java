/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

import org.tigris.subversion.svnclientadapter.SVNClientException;

import junit.framework.TestCase;

public class ResourceParentNotVersionedExceptionTest extends TestCase {

	public void testIdentifyJavahl() {
		String path = "C:\\Documents and Settings\\Temp\\testAddNotRecursive1162545488103";
		String message = "Path is not a working copy directory\n" +
		"svn: '" + path + "' is not a working copy\n" +
		"Det g�r inte att hitta s�kv�gen.\n" +
		"svn: Can't open file '" + path + "\\.svn\\entries': Det g�r inte att hitta s�kv�gen.";
		
		try {
			ResourceParentNotVersionedException.identify(new SVNClientException(message));
			fail("Should have identified this as a 'parent not under version control' error");
		} catch (ResourceParentNotVersionedException e) {
			assertEquals(new File(path), e.getPath());
		}
	}

}
