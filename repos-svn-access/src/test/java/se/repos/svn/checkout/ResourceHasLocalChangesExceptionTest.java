/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

import org.tigris.subversion.svnclientadapter.SVNClientException;

import junit.framework.TestCase;

public class ResourceHasLocalChangesExceptionTest extends TestCase {

	public void testIdentifyNot() {
		String message = "" +
		"Got some unknown error\n" +
		"svn: 'C:\\test' is not working";
		ResourceHasLocalChangesException.identify(new SVNClientException(message));
	}
	
	public void testIdentifyJavahl() {
		String path = "C:\\Documents and Settings\\solsson\\Lokala inställningar\\Temp\\folder\\to be deleted.txt";
		String message = "\n" +
		"Attempting restricted operation for modified resource\n" +
		"svn: '" + path + "' has local modifications\n";
		
		try {
			ResourceHasLocalChangesException.identify(new SVNClientException(message));
			fail("Should have identified this as a 'has local modifications' error");
		} catch (ResourceHasLocalChangesException e) {
			assertEquals(new File(path), e.getPath());
		}
	}
	
	public void testIdentifyJavaSVN() {
		String message = "svn: 'to be deleted.txt' has local modifications";
		try {
			ResourceHasLocalChangesException.identify(new SVNClientException(message));
			fail("Should have identified this as a 'has local modifications' error");
		} catch (ResourceHasLocalChangesException e) {
			assertEquals(new File("to be deleted.txt"), e.getPath());
		}		
	}

}
