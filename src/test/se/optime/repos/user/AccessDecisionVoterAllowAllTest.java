/*
 * Created on 2004-okt-10
 */
package se.optime.repos.user;

import net.sf.acegisecurity.vote.AccessDecisionVoter;
import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class AccessDecisionVoterAllowAllTest extends TestCase {

    public void testVote() {
        assertEquals("grant everyone",AccessDecisionVoter.ACCESS_GRANTED,
                new se.optime.repos.user.AccessDecisionVoterAllowAll().vote(null,null,null));
    }

}
