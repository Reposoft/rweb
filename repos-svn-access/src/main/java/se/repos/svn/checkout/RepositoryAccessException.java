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
	
	private static final long serialVersionUID = 1L;
	
	/**
	 * To be used instead of constructor.
	 * Allowes transparent use of an exception hierarchy.
	 * @param e cause
	 * @throws RepositoryAccessException
	 */
	public static final void handle(SVNClientException e)
		throws RepositoryAccessException {
		InvalidCredentialsException.identify(e);
		throw new RepositoryAccessException(e);
	}
	
	protected RepositoryAccessException(SVNClientException e) {
		super(e);
	}
	
	//currently not used
	protected RepositoryAccessException(String message) {
		super(message);
	}

}
