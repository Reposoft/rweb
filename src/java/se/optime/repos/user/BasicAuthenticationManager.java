/*
 * Created on Sep 22, 2004
 */
package se.optime.repos.user;

import net.sf.acegisecurity.Authentication;
import net.sf.acegisecurity.AuthenticationCredentialsNotFoundException;
import net.sf.acegisecurity.AuthenticationException;
import net.sf.acegisecurity.AuthenticationManager;
import net.sf.acegisecurity.AuthenticationServiceException;
import net.sf.acegisecurity.GrantedAuthority;
import net.sf.acegisecurity.GrantedAuthorityImpl;
import net.sf.acegisecurity.providers.UsernamePasswordAuthenticationToken;

/**
 * Assuming a UsernamePasswordAuthenticationToken and setting authority USER
 * @author solsson
 * @version $Id$
 */
public class BasicAuthenticationManager implements AuthenticationManager {

    /* (non-Javadoc)
     * @see net.sf.acegisecurity.AuthenticationManager#authenticate(net.sf.acegisecurity.Authentication)
     */
    public Authentication authenticate(Authentication authentication)
            throws AuthenticationException {
        
        if (authentication==null)
            throw new AuthenticationServiceException("AuthenticationMissing");
        
        if (authentication.getPrincipal()==null || authentication.getPrincipal().toString().length()==0)
            throw new AuthenticationCredentialsNotFoundException("UsernameMissing");
        
        // all users have the same role
        //((UsernamePasswordAuthenticationToken)authentication).setAuthorities(
        //        new GrantedAuthority[] {new GrantedAuthorityImpl("USER"),new GrantedAuthorityImpl("ROLE_USER")});
        
        // all usernames are accepted
        authentication.setAuthenticated(true);
        
        return authentication;
    }

}
