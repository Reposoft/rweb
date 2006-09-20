/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.IOException;

import org.tigris.subversion.svnclientadapter.SVNClientException;

/**
 * Thrown when an offline operation fails.
 * 
 * This is considered a file system problem or a program error, 
 * and is therefore thrown as a runtime exception.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @see RepositoryAccessException
 */
public class WorkingCopyAccessException extends RuntimeException {

	private static final long serialVersionUID = 1L;

	public WorkingCopyAccessException(SVNClientException e) {
		super(e);
	}

	public WorkingCopyAccessException(IOException e) {
		super(e);
	}

}
