/*
 * Created on Sep 23, 2004
 */
package se.optime.repos;

import se.optime.repos.calendar.IcalParserTest;
import se.optime.repos.document.DocumentControllerTest;
import se.optime.repos.tags.StreamOutputTagTest;
import se.optime.repos.user.AccessDecisionVoterAllowAllTest;
import se.optime.repos.user.BasicAuthenticationManagerTest;
import se.optime.repos.user.BasicAuthenticationResolverTest;
import se.optime.repos.webdav.ConnectionExceptionTest;
import se.optime.repos.webdav.DoesNotExistExceptionTest;
import se.optime.repos.webdav.RepositoryPathValidatorTest;
import se.optime.repos.webdav.RepositoryResourceTest;
import junit.framework.Test;
import junit.framework.TestSuite;
import junit.textui.TestRunner;

/**
 * @author solsson
 * @version $Id$
 */
public class ReposUnitTests extends TestSuite {

    public static void main(String args[]) {
        TestRunner.run(suite());
    }
    
    public static Test suite() {
        TestSuite suite = new TestSuite("All Repos unit tests");
        // alphabetical order - insertion sort
        suite.addTest(new TestSuite(AccessDecisionVoterAllowAllTest.class));
        suite.addTest(new TestSuite(BasicAuthenticationManagerTest.class));
        suite.addTest(new TestSuite(BasicAuthenticationResolverTest.class));
        suite.addTest(new TestSuite(ConnectionExceptionTest.class));
        suite.addTest(new TestSuite(DocumentControllerTest.class));
        suite.addTest(new TestSuite(DoesNotExistExceptionTest.class));
        suite.addTest(new TestSuite(IcalParserTest.class));
        suite.addTest(new TestSuite(RepositoryAccessExceptionTest.class));
        suite.addTest(new TestSuite(RepositoryPathValidatorTest.class));
        suite.addTest(new TestSuite(RepositoryResourceTest.class));
        suite.addTest(new TestSuite(StreamOutputTagTest.class));
        //suite.addTest(new TestSuite(.class));     
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));        
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));        
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));        
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));        
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));
        //suite.addTest(new TestSuite(.class));        
        return suite;
    }
    
}
