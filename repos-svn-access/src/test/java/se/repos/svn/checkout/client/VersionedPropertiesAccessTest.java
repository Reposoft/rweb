/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.io.IOException;

import org.easymock.MockControl;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;

import se.repos.svn.checkout.VersionedProperties;

import junit.framework.TestCase;

public class VersionedPropertiesAccessTest extends TestCase {
	
	public void testGetPath() throws IOException {
		MockControl clientControl = MockControl.createControl(ISVNClientAdapter.class);
		ISVNClientAdapter client = (ISVNClientAdapter) clientControl.getMock();
		
		File path = File.createTempFile("repos-svn-access", "versionedPropertiesTest");
		VersionedProperties prop = new PropertyAccess(path, client);
	}

	public void testSetIgnoreFile() {
		fail("Not yet implemented");
	}

}
