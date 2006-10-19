/* Copyright 2006 Optime data Sweden
 */
package se.repos.svn;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientException;

/**
 * Represents a choice of svn client library, and the initialization logic for it.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo add handling of client library not available, for example an exception
 */
public interface ClientProvider {
	
	/**
	 * Provides an initialized svnClient to the application.
	 *
	 * @return A client ready to do svn operations. Never returns null.
	 * @throws Runtime exception if the client can not be started.
	 * The constructor should throw checked exception if this can be foreseen.
	 */
	ISVNClientAdapter getSvnClient();
	
	/**
	 * Provides an initialized svnClient with user account to the application.
	 *
	 * @param login The intended user's login
	 * @return A client ready to do svn operations for the user
	 */
	ISVNClientAdapter getSvnClient(UserCredentials login);
	
	/**
	 * Exception thrown by initializer if the client can not be created,
	 * for example if the library is not available.
	 */
	public static class ClientNotAvaliableException extends Exception {
		private static final long serialVersionUID = 1L;
		public ClientNotAvaliableException(SVNClientException e) {
			super(e);
		}
		public ClientNotAvaliableException(String message, SVNClientException e) {
			super(message, e);
		}
	}
	
}
