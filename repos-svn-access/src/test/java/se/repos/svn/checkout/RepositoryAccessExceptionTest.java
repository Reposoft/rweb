/* $license_header$
 */
package se.repos.svn.checkout;

import org.tigris.subversion.svnclientadapter.SVNClientException;

import junit.framework.TestCase;

public class RepositoryAccessExceptionTest extends TestCase {

	public void testHandleNormal() {
		try {
			RepositoryAccessException.handle(new SVNClientException());
			fail("Should throw the standard exception");
		} catch (RepositoryAccessException e) {
			assertEquals(RepositoryAccessException.class, e.getClass());
		}
	}
	
	public void testHandleSSLException() {
		
	}
	
	public void testHandleCertificateIssuerNotTrusted() {
		String message = "RA layer request failed\n" +
			"svn: PROPFIND request failed on '/testrepo/test/trunk/repos-svn-access'\n" +
			"svn: PROPFIND of '/testrepo/test/trunk/repos-svn-access': Server certificate verification failed: issuer is not trusted (https://localhost)";
		// TODO
	}

}
