/* $license_header$
 */
package se.repos.svn.checkout.managed;

import junit.framework.Test;
import junit.framework.TestSuite;

public class AllTests {

	public static Test suite() {
		TestSuite suite = new TestSuite(
				"Test for se.repos.svn.checkout.managed");
		//$JUnit-BEGIN$
		suite.addTestSuite(DefaultReposClientSettingsTest.class);
		suite.addTestSuite(ManagedWorkingCopyIntegrationTest.class);
		suite.addTestSuite(DefaultClientConfigurationIntegrationTest.class);
		suite.addTestSuite(SSLIntegrationTest.class);
		//$JUnit-END$
		return suite;
	}

}
