/*
 * Created on Sep 22, 2004
 */
package se.optime.repos.user;

import net.sf.acegisecurity.Authentication;
import net.sf.acegisecurity.AuthenticationServiceException;
import net.sf.acegisecurity.context.ContextHolder;
import net.sf.acegisecurity.context.SecureContextImpl;
import net.sf.acegisecurity.providers.UsernamePasswordAuthenticationToken;
import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class BasicAuthenticationResolverTest extends TestCase {

    AuthenticationResolver ar = null;
    
    /*
     * @see TestCase#setUp()
     */
    protected void setUp() throws Exception {
        SecureContextImpl sc = new SecureContextImpl();
        ContextHolder.setContext(sc);
        sc.setAuthentication(new UsernamePasswordAuthenticationToken("username","password"));
        ar = new BasicAuthenticationResolver();
    }

    /*
     * @see TestCase#tearDown()
     */
    protected void tearDown() throws Exception {
        super.tearDown();
    }

    public void testGetAuthentication() throws Exception {
        Authentication a = ar.getAuthentication();
        assertNotNull("Context's authentication",a);
    }
    
    public void testGetAuthenticationNull() throws Exception {
        // override default setup
        SecureContextImpl sc = new SecureContextImpl();
        ContextHolder.setContext(sc);
        sc.setAuthentication(null);
        Authentication a;
        try {
            a = ar.getAuthentication();
            fail("Authentication exception should be thrown for null authentication object");
        } catch (AuthenticationServiceException e) {
        }
    }
    
    public void testGetAuthenticatedUsername() throws Exception {
        assertEquals("username",ar.getAuthenticatedUsername());
    }
    
    public void testGetAuthenticatedPassword() throws Exception {
        assertEquals("password",ar.getAuthenticatedPassword());
    }
    
    public void testGetBasicAuthenticationString() throws Exception {
        assertEquals("dXNlcm5hbWU6cGFzc3dvcmQ=",ar.getBasicAuthenticationString());
    }
}
