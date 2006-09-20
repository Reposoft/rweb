/* $license_header$
 */
package se.repos.svn.checkout;

import org.tigris.subversion.svnclientadapter.SVNClientException;

/**
 * Thrown when an online operation fails.
 * 
 * This is most likely a network error or an invalid URL
 * that the application must be able to recover from.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @see WorkingCopyAccessException
 */
public class RepositoryAccessException extends Exception {
	public RepositoryAccessException(SVNClientException e) {
		super(e);
	}

	private static final long serialVersionUID = 1L;

}
