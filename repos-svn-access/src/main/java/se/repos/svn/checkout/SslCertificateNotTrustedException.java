/* $license_header$
 */
package se.repos.svn.checkout;

import org.tigris.subversion.svnclientadapter.SVNClientException;


/**
 * Aborts the connection because the issuer of the SSL certificate is not trusted.
 * 
 * The repos client do not authenticate using SSL, they merely want encrypted transfer.
 * So the client should automatically accept certificates, regardless of issuer,
 * unless there is a {@link SslCertificateHostMismatchException}.
 * 
 * Matches with javahl:
 * <pre>
se.repos.svn.checkout.RepositoryAccessException: org.tigris.subversion.svnclientadapter.SVNClientException: org.tigris.subversion.javahl.ClientException: RA layer request failed
svn: PROPFIND request failed on '/testrepo/test/trunk/repos-svn-access'
svn: PROPFIND of '/testrepo/test/trunk/repos-svn-access': Server certificate verification failed: issuer is not trusted (https://localhost)
 * </pre>
 * 
 * SvnKit accepts this error siletly and proceeds with the transaction.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class SslCertificateNotTrustedException extends
		SslConnectionException {

	private static final long serialVersionUID = 1L;

	public SslCertificateNotTrustedException(SVNClientException e) {
		super(e);
		// TODO auto-generated
	}

}
