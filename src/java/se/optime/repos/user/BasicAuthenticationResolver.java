/*
 * Created on Sep 22, 2004
 */
package se.optime.repos.user;

import org.apache.commons.codec.binary.Base64;

import net.sf.acegisecurity.Authentication;
import net.sf.acegisecurity.AuthenticationServiceException;
import net.sf.acegisecurity.context.ContextHolder;
import net.sf.acegisecurity.context.SecureContext;

/**
 * @author solsson
 * @version $Id$
 */
public class BasicAuthenticationResolver implements AuthenticationResolver {

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getAuthentication()
     */
    public Authentication getAuthentication()
            throws AuthenticationServiceException {
		SecureContext context = (SecureContext) ContextHolder.getContext();
		if (context==null)
			throw new AuthenticationServiceException("SecureContextMissing");
		Authentication auth = context.getAuthentication();
		if (auth==null)
		    throw new AuthenticationServiceException("AuthenticationMissing");
		return auth;
    }

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getAuthenticatedUsername()
     */
    public String getAuthenticatedUsername() {
        return ""+this.getAuthentication().getPrincipal();
    }

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getAuthenticatedPassword()
     */
    public String getAuthenticatedPassword() {
        return ""+this.getAuthentication().getCredentials();
    }

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getBasicAuthenticationString()
     */
    public String getBasicAuthenticationString() {
        StringBuffer b = new StringBuffer().append(this.getAuthenticatedUsername()).append(':').append(this.getAuthenticatedPassword());
        byte[] e = Base64.encodeBase64(b.toString().getBytes());
        return new String(e);
    }

}
