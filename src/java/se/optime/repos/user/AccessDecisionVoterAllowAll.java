/*
 * Created on Sep 22, 2004
 */
package se.optime.repos.user;

import net.sf.acegisecurity.Authentication;
import net.sf.acegisecurity.ConfigAttribute;
import net.sf.acegisecurity.ConfigAttributeDefinition;
import net.sf.acegisecurity.vote.AccessDecisionVoter;

/**
 * @author solsson
 * @version $Id$
 */
public class AccessDecisionVoterAllowAll implements AccessDecisionVoter {

    /* (non-Javadoc)
     * @see net.sf.acegisecurity.vote.AccessDecisionVoter#supports(net.sf.acegisecurity.ConfigAttribute)
     */
    public boolean supports(ConfigAttribute attribute) {
        return true;
    }

    /* (non-Javadoc)
     * @see net.sf.acegisecurity.vote.AccessDecisionVoter#supports(java.lang.Class)
     */
    public boolean supports(Class clazz) {
        return true;
    }

    /* (non-Javadoc)
     * @see net.sf.acegisecurity.vote.AccessDecisionVoter#vote(net.sf.acegisecurity.Authentication, java.lang.Object, net.sf.acegisecurity.ConfigAttributeDefinition)
     */
    public int vote(Authentication authentication, Object object, ConfigAttributeDefinition config) {
        return ACCESS_GRANTED;
    }

}
