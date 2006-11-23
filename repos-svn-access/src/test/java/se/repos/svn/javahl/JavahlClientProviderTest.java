/* $license_header$
 */
package se.repos.svn.javahl;

import java.io.File;

import se.repos.svn.ClientProvider;
import se.repos.svn.ClientProvider.ClientNotAvaliableException;
import se.repos.svn.test.TestFolder;
import junit.framework.TestCase;

public class JavahlClientProviderTest extends TestCase {

	public void testCreateDefaultConfiguration() {
		
		File configFolder = TestFolder.getNew();
		configFolder.delete(); // can't have an empty config folder
		
		ClientProvider provider;
		try {
			provider = JavahlClientProvider.getProvider();
		} catch (ClientNotAvaliableException e) {
			// OK, can't test this if javahl is not available
			System.out.println("Can not test javahl client creation, because javahl is not available");
			return;
		}
		
		assertFalse("This test should start with a non-existing config area folder", configFolder.exists());
		provider.getSvnClient(configFolder); // calls setConfigDir
		assertTrue("After the client is initialized with a custom folder, contents should have been created", configFolder.exists());
		
		assertTrue("Should find 'config' file", new File(configFolder, "config").exists());
		assertTrue("Should find 'servers' file", new File(configFolder, "servers").exists());
	}

}
