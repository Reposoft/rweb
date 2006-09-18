/* Copyright 2006 Optime data Sweden
 */
package se.repos.svn;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;

/**
 * Represents a choice of svn client library, and the initialization logic for it.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface ClientProvider {

	/**
	 * Provides an initialized svnClient to the application.
	 *
	 * @return A client ready to do svn operations
	 */
	ISVNClientAdapter getSvnClient();
	
	/**
	 * Provides an initialized svnClient with user account to the application.
	 *
	 * @param login The intended user's login
	 * @return A client ready to do svn operations for the user
	 */
	ISVNClientAdapter getSvnClient(UserCredentials login);
	
}
