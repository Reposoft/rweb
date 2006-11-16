/* $license_header$
 */
package se.repos.svn.checkout.client;

import junit.framework.Test;
import junit.framework.TestSuite;

public class AllTests {

	public static Test suite() {
		TestSuite suite = new TestSuite("Test for se.repos.svn.checkout.client");
		//$JUnit-BEGIN$
		suite.addTestSuite(AbstractCheckoutSettingsTest.class);
		suite.addTestSuite(VersionedPropertiesAccessTest.class);
		suite.addTestSuite(ReposWorkingCopySvnTest.class);
		suite.addTestSuite(ConflictHandlerStandardTest.class);
		//$JUnit-END$
		return suite;
	}

}
