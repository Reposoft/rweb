/* $license_header$
 */
package se.repos.svn.checkout;

import junit.framework.Test;
import junit.framework.TestSuite;

public class AllTests {

	public static Test suite() {
		TestSuite suite = new TestSuite("Test for se.repos.svn.checkout");
		//$JUnit-BEGIN$
		suite.addTestSuite(RepositoryAccessExceptionTest.class);
		suite.addTestSuite(ResourceParentNotVersionedExceptionTest.class);
		suite.addTestSuite(ResourceHasLocalChangesExceptionTest.class);
		suite.addTestSuite(ConflictExceptionTest.class);
		suite.addTestSuite(ResourceNotVersionedExceptionTest.class);
		//$JUnit-END$
		return suite;
	}

}
