/* $license_header$
 */
package se.repos.svn.config;

import se.repos.svn.config.file.ConfigFileTest;
import se.repos.svn.config.file.IniFileTest;
import se.repos.svn.config.file.ServersFileTest;
import junit.framework.Test;
import junit.framework.TestSuite;

public class AllTests {

	public static Test suite() {
		TestSuite suite = new TestSuite("Test for se.repos.svn.config");
		//$JUnit-BEGIN$
		suite.addTestSuite(RuntimeConfigurationAreaTest.class);
		//$JUnit-END$
		suite.addTestSuite(IniFileTest.class);
		suite.addTestSuite(ConfigFileTest.class);
		suite.addTestSuite(ServersFileTest.class);
		return suite;
	}

}
