/* $license_header$
 */
package se.repos.svn;

import java.io.File;
import java.net.MalformedURLException;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientAdapterFactory;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.SVNRevision;
import org.tigris.subversion.svnclientadapter.SVNUrl;
import org.tigris.subversion.svnclientadapter.javahl.JhlClientAdapterFactory;

import junit.framework.TestCase;

public class TestConfigDirectory extends TestCase {

	public void testEnable() {
		System.out.println("This test checks for an issue with setConfigDirectory and should be ignored.");
		//ignore_testCache();
	}
	
	public void ignore_testCache() throws MalformedURLException, SVNClientException {
		
		File configFolder = new File("configarea");
		File wc = new File("wc");
		
		// can not be run in a test suite with the shared provider
		JhlClientAdapterFactory.setup();
		ISVNClientAdapter client = SVNClientAdapterFactory.createSVNClient(
				JhlClientAdapterFactory.JAVAHL_CLIENT);
		
		client.setConfigDirectory(configFolder);
		assertTrue("Should have created default config", configFolder.exists());
		
		try {
			client.setUsername("test");
			client.checkout(
					new SVNUrl("http://localhost/testrepo/test/trunk/"), 
					wc, SVNRevision.HEAD, true);
		} catch (SVNClientException e) {
			// expected, no password set
		}
		
		assertFalse("invalid login -> no checkout", new File(wc, ".svn").exists());
	}
	
}
