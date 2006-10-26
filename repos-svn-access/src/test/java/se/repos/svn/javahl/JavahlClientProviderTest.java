/* $license_header$
 */
package se.repos.svn.javahl;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;

import se.repos.svn.ClientProvider;
import se.repos.svn.ClientProvider.ClientNotAvaliableException;
import junit.framework.TestCase;

public class JavahlClientProviderTest extends TestCase {

	public void testGetRuntimeConfigurationArea() {
		ClientProvider clientProvider;
		try {
			clientProvider = new JavahlClientProvider();
		} catch (ClientNotAvaliableException e) {
			// Javahl native library is not required, this test runs only when possible
			return;
		}
		ISVNClientAdapter svnClient = clientProvider.getSvnClient(); // must be done first
		assertNotNull("Javahl requires an svnClient to get runtime configuration area", svnClient);
		File area = clientProvider.getRuntimeConfigurationArea();
		System.out.println("JavaSVN retuned config folder: " + area.getAbsolutePath());
		// assuming that the test system has a configuration area
		assertNotNull("Should return configuration area from client lib", area);
		assertTrue("Folder should exist", area.exists() && area.isDirectory());
		assertTrue("Configuration area should contain a 'servers' file", new File(area, "servers").exists());
	}

}
