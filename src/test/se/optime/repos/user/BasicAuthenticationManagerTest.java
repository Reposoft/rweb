/*
 * Created on Sep 22, 2004
 */
package se.optime.repos.user;

import net.sf.acegisecurity.AuthenticationException;
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
            return;
        } 
        catch (RuntimeException e) {
            return;
        }
        fail("Should have thrown an exception for null input");
    }

}
