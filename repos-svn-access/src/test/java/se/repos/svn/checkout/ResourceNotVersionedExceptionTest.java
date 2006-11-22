/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

import org.tigris.subversion.svnclientadapter.SVNClientException;

import junit.framework.TestCase;

public class ResourceNotVersionedExceptionTest extends TestCase {

	public void testIdentifyNot() {
		String message = "" +
		"Got some unknown error\n" +
		"svn: 'C:\\test' is not working";
		ResourceNotVersionedException.identify(new SVNClientException(message));
	}
	
	public void testIdentifyJavahl() {
		String path = "C:\\Documents and Settings\\solsson\\Lokala inställningar\\Temp\\folder\\nonversioned.txt";
		String message = "\n" +
		"Tried a versioning operation on an unversioned resource\n" +
		"svn: '" + path + "' is not under version control\n";
		
		try {
			ResourceNotVersionedException.identify(new SVNClientException(message));
			fail("Should have identified this as a 'not under version control' error");
		} catch (ResourceNotVersionedException e) {
			assertEquals(new File(path), e.getPath());
		}
	}
	
	public void testIdentifySvnKit() {
		String message = "svn: '/tmp/file' is not under version control";
		try {
			ResourceNotVersionedException.identify(new SVNClientException(message));
			fail("Should have identified this as a 'not under version control' error");
		} catch (ResourceNotVersionedException e) {
			assertEquals(new File("/tmp/file"), e.getPath());
		}		
	}

}
