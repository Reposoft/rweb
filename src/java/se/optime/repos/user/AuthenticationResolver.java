/*
 * Created on Sep 22, 2004
 */
package se.optime.repos.user;

import net.sf.acegisecurity.Authentication;
import net.sf.acegisecurity.AuthenticationServiceException;

/**
 * @author solsson
 * @version $Id$
 */
public interface AuthenticationResolver {

	/**
	 * Get authentication object from current request context
	 * @return Not-null authentication instance
	 * @throws AuthenticationServiceException if authentication is not in order (a request should not get this far without successful authentication)
	 */
	public Authentication getAuthentication() 
		throws AuthenticationServiceException;
	
	/**
	 * @return the username for this request
	 */
	public String getAuthenticatedUsername();
	
	/**
	 * @return the password for this request
	 */
	public String getAuthenticatedPassword();
	
	/**
	 * @return the base64 encoded username:password
	 */
	public String getBasicAuthenticationString();
    
}
