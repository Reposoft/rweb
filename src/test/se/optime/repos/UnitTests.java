/*
 * Created on Sep 23, 2004
 */
package se.optime.repos;

import se.optime.repos.calendar.IcalParserTest;
import se.optime.repos.user.BasicAuthenticationManagerTest;
import se.optime.repos.user.BasicAuthenticationResolverTest;
import se.optime.repos.webdav.RepositoryResourceTest;
import junit.framework.Test;
import junit.framework.TestSuite;
import junit.textui.TestRunner;

/**
 * @author solsson
 * @version $Id$
 */
public class UnitTests extends TestSuite {

    public static void main(String args[]) {
        TestRunner.run(suite());
    }
    
    public static Test suite() {
        TestSuite suite = new TestSuite("All Repos unit tests");
        suite.addTest(new TestSuite(BasicAuthenticationManagerTest.class));
        suite.addTest(new TestSuite(BasicAuthenticationResolverTest.class));
        suite.addTest(new TestSuite(IcalParserTest.class));
        suite.addTest(new TestSuite(RepositoryResourceTest.class));
        return suite;
    }
    
}
