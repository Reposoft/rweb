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

	// verify an issue with setConfigDirectory
	public void testCache() throws MalformedURLException, SVNClientException {
		
		File configFolder = new File("configarea");
		File wc = new File("wc");
		
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
