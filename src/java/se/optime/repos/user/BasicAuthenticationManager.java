/*
 * Created on Sep 22, 2004
 */
package se.optime.repos.user;

import net.sf.acegisecurity.Authentication;
import net.sf.acegisecurity.AuthenticationException;
import net.sf.acegisecurity.AuthenticationManager;

/**
 * @author solsson
 * @version $Id$
 */
public class BasicAuthenticationManager implements AuthenticationManager {

    /* (non-Javadoc)
     * @see net.sf.acegisecurity.AuthenticationManager#authenticate(net.sf.acegisecurity.Authentication)
     */
    public Authentication authenticate(Authentication arg0)
            throws AuthenticationException {
        // TODO Auto-generated method stub
        throw new java.lang.UnsupportedOperationException(
                "Method BasicAuthenticationManager.authenticate not implemented");
    }

}
