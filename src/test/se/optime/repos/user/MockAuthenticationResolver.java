/*
 * Created on 2004-okt-02
 */
package se.optime.repos.user;

import net.sf.acegisecurity.Authentication;
import net.sf.acegisecurity.AuthenticationServiceException;
import net.sf.acegisecurity.providers.UsernamePasswordAuthenticationToken;

/**
 * @author solsson
 * @version $Id$
 */
public class MockAuthenticationResolver implements AuthenticationResolver {

    private boolean userChecked = false;
    private boolean passChecked = false;
    public static final String TESTUSER = "testuser";
    public static final String TESTPASS = "testpass";
    public static final String ENCODED = "mumbo:jumbo";
    private boolean authenticationChecked = false;
    
    /**
     * Set up test authentication
     */
    public MockAuthenticationResolver() {
        StaticAuthenticationResolver.setResolver(this);
    }
    
    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getAuthentication()
     */
    public Authentication getAuthentication()
            throws AuthenticationServiceException {
        authenticationChecked = true;
        Authentication a = new UsernamePasswordAuthenticationToken(TESTUSER,TESTPASS);
        a.setAuthenticated(true);
        return a;
    }

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getAuthenticatedUsername()
     */
    public String getAuthenticatedUsername() {
        userChecked = true;
        return TESTUSER;
    }

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getAuthenticatedPassword()
     */
    public String getAuthenticatedPassword() {
        passChecked = true;
        return TESTPASS;
    }

    /* (non-Javadoc)
     * @see se.optime.repos.user.AuthenticationResolver#getBasicAuthenticationString()
     */
    public String getBasicAuthenticationString() {
        return ENCODED;
    }
    
    public void assertAskedFor() {
        if (authenticationChecked)
            return;
        if (!userChecked)
            junit.framework.Assert.fail("Username not asked for");
        if (!passChecked)
            junit.framework.Assert.fail("Password not asked for");
    }
    
    public void assertUserNotAskedFor() {
        if (userChecked)
            junit.framework.Assert.fail("Username was asked for");
        if (passChecked)
            junit.framework.Assert.fail("Password was asked for");        
    }

}
