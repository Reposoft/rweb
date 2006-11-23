/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.io.IOException;

import junit.framework.TestCase;

import org.easymock.MockControl;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientException;

import se.repos.svn.checkout.VersionedProperties;

public class VersionedPropertiesAccessTest extends TestCase {
	
	public void testGetPath() throws IOException, SVNClientException {
		MockControl clientControl = MockControl.createControl(ISVNClientAdapter.class);
		ISVNClientAdapter client = (ISVNClientAdapter) clientControl.getMock();
		
		File path = File.createTempFile("repos-svn-access", "versionedPropertiesTest");
		VersionedProperties prop = new PropertyAccess(path, client);
		
		// hmm, the old advice "don't mock other people's code" is good. This test is either useless or too complex.
		client.getProperties(path);
		clientControl.setReturnValue(null);
		clientControl.replay();
		
		try {
			prop.getProperty("my:hepp");
		} catch (Throwable t) {
			// nullpointer or something
		}
		path.delete();
	}
	
	public void testExists() throws IOException, SVNClientException {
		MockControl clientControl = MockControl.createControl(ISVNClientAdapter.class);
		ISVNClientAdapter client = (ISVNClientAdapter) clientControl.getMock();
		
		File path = File.createTempFile("repos-svn-access", "versionedPropertiesTest");
		VersionedProperties prop = new PropertyAccess(path, client);
		
		client.propertyGet(path, "myproperty");
		clientControl.setReturnValue(null); // means property not found in svnClientAdapter
		clientControl.replay();
		boolean has = prop.hasProperty("myproperty");
		clientControl.verify();
		assertFalse("in svnClientAdapterh, property=null means property not set", has);
	}

}
