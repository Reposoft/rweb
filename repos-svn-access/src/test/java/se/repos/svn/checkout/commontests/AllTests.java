/* $license_header$
 */
package se.repos.svn.checkout.commontests;

import junit.framework.Test;
import junit.framework.TestSuite;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.ReposWorkingCopyFactory;

/**
 * Note that the test runner must be set to Junit 3 in Eclipse Run configuration.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class AllTests {

	private static String now = "";
	
	public static ReposWorkingCopy getClient(CheckoutSettings settings, String name) {
		if (name!=null && !name.equals(now)) {
			System.out.println("----------- " + name + " ----------");
		}
		now = name;
		return ReposWorkingCopyFactory.getClient(settings);
	}
	
	public static Test suite() {
		TestSuite suite = new TestSuite(
				"Test for se.repos.svn.checkout.commontests");
		//$JUnit-BEGIN$
		suite.addTestSuite(CheckoutUpdateCommitIntegrationTest.class);
		suite.addTestSuite(StatusAddDeleteIntegrationTest.class);
		suite.addTestSuite(MoveCopyRevertIntegrationTest.class);
		suite.addTestSuite(ConflictHandlingIntegrationTest.class);
		suite.addTestSuite(SvnPropertiesIntegrationTest.class);
		//$JUnit-END$
		return suite;
	}

}
