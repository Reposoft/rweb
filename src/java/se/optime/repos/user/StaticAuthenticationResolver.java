/*
 * Created on 2004-okt-02
 */
package se.optime.repos.user;

import net.sf.acegisecurity.Authentication;
import net.sf.acegisecurity.AuthenticationServiceException;

/**
 * Providing access to the current user credentials from a command instance.
 * 
 * <p>Just delgates to an AuthenticationResolver. The default instance is a
 * {@link BasicAuthenticationResolver}.
 * This can be changed (global) using the {@link #setResolver(AuthenticationResolver) setResolver} method.
 * 
 * @author solsson
 * @version $Id$
 */
public class StaticAuthenticationResolver {

    private static AuthenticationResolver resolver = 
        new BasicAuthenticationResolver();
    
    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getAuthentication()
     */
    public static Authentication getAuthentication()
            throws AuthenticationServiceException {
        return resolver.getAuthentication();
    }

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getAuthenticatedUsername()
     */
    public static String getAuthenticatedUsername() {
        return resolver.getAuthenticatedUsername();
    }

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getAuthenticatedPassword()
     */
    public static String getAuthenticatedPassword() {
        return resolver.getAuthenticatedPassword();
    }

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getBasicAuthenticationString()
     */
    public static String getBasicAuthenticationString() {
        return resolver.getBasicAuthenticationString();
    }

    /**
     * @param resolver The resolver to set.
     */
    protected static void setResolver(AuthenticationResolver resolver) {
        StaticAuthenticationResolver.resolver = resolver;
    }
}
