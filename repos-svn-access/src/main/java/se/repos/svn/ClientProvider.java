/* Copyright 2006 Optime data Sweden
 */
package se.repos.svn;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientException;

import se.repos.svn.config.ClientConfiguration;
import se.repos.svn.config.ConfigurationStateException;

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
	 * This operation does the setup of the client,
	 * so it should be called once and kept throughout the user's work session.
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
	 * Supplies a read-write model of the runtime configuration area for the client.
	 * 
	 * Applications trust that changing this configuration affects the behaviours of the initialized clients.
	 * 
	 * @return The configuration for the subversion client
	 */
	ClientConfiguration getRuntimeConfiguration() throws ConfigurationStateException;
	
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
