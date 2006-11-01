/* $license_header$
 */
package se.repos.svn.checkout;

import org.tigris.subversion.svnclientadapter.SVNClientException;

public class SslConnectionException extends RepositoryAccessException {

	protected SslConnectionException(SVNClientException e) {
		super(e);
		// TODO auto-generated
	}

	private static final long serialVersionUID = 1L;

}
