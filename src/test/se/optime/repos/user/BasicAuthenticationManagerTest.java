/*
 * Created on Sep 22, 2004
 */
package se.optime.repos.user;

import net.sf.acegisecurity.Authentication;
import net.sf.acegisecurity.AuthenticationException;
import net.sf.acegisecurity.AuthenticationServiceException;
import net.sf.acegisecurity.providers.UsernamePasswordAuthenticationToken;
import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class BasicAuthenticationManagerTest extends TestCase {

    BasicAuthenticationManager authenticationManager = null;
    
    /*
     * @see TestCase#setUp()
     */
    protected void setUp() throws Exception {
        super.setUp();
        authenticationManager = new BasicAuthenticationManager();
    }

    /*
     * @see TestCase#tearDown()
     */
    protected void tearDown() throws Exception {
        super.tearDown();
    }

    public void testAuthenticateNull() {
        try {
            authenticationManager.authenticate(null);
        } catch (AuthenticationException e) {
            assertTrue("Should throw system error on null input", e.getClass().isAssignableFrom(AuthenticationServiceException.class));
            return;
        } 
        fail("Should have thrown an exception for null input");
    }
    
    public void testAuthenticateUsernamePassword() {
        Authentication a = new UsernamePasswordAuthenticationToken("username","password");
        assertFalse("Not authenticated before processing",a.isAuthenticated());
        Authentication auth = null;
        try {
            auth = authenticationManager.authenticate(a);
        } catch (AuthenticationException e) {
            fail("Authentication should not fail, but threw " + e);
        }
        //assertTrue("All usernames should be accepted",auth.isAuthenticated());
        assertEquals("Principal = username","username",a.getPrincipal());
        assertEquals("Password should be preserved","password",a.getCredentials());
        //assertNotNull("Not-null granted authority",a.getAuthorities());
    }
    
    public void testAuthenticateEmpty() {
        Authentication a = new UsernamePasswordAuthenticationToken("","password");
        Authentication auth = null;
        try {
            auth = authenticationManager.authenticate(a);
            fail("Authentication exception shuld have been thrown for empty username");
        } catch (AuthenticationException e) {
            System.out.print("[test-debug] expected exception: " + e.toString());
        }
        assertFalse("Empty username should not be accepted",a.isAuthenticated());
    }
}
