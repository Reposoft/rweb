/* $license_header$
 */
package se.repos.svn.checkout;

import org.tigris.subversion.svnclientadapter.SVNClientException;

/**
 * Repository web server configuration is invalid if certificate hostname does not match actual hostname.
 * 
 * Command line client can accept the certificate temporarily, but in repos
 * it is considered a setup error and the client does not attempt to access the repository.
 * 
 * Message from javahl:
 * <pre>
se.repos.svn.checkout.RepositoryAccessException: org.tigris.subversion.svnclientadapter.SVNClientException: org.tigris.subversion.javahl.ClientException: RA layer request failed
svn: PROPFIND request failed on '/testrepo/test/trunk/repos-svn-access'
svn: PROPFIND of '/testrepo/test/trunk/repos-svn-access': Server certificate verification failed: certificate issued for a different hostname, issuer is not trusted (https://localhost)
 * </pre>
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @see SslCertificateNotTrustedException
 */
public class SslCertificateHostMismatchException extends
		SslConnectionException {

	private static final long serialVersionUID = 1L;

	public SslCertificateHostMismatchException(SVNClientException e) {
		super(e);
		// TODO auto-generated
	}

}
