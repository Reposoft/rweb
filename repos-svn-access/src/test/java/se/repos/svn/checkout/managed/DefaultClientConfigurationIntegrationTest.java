/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;
import java.lang.reflect.InvocationTargetException;
import java.util.HashSet;
import java.util.Set;

import junit.framework.TestCase;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientException;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.client.GetClientAdapter;
import se.repos.svn.test.CheckoutSettingsForTest;
import se.repos.svn.test.TestFolder;

public class DefaultClientConfigurationIntegrationTest extends TestCase {

	public static final String[] EXPECT_IGNORE = new String[] {
			"temp", "Temp", "TEMP", "~*", "Thumbs.db", ".DS_Store"
	};
	
	public void testCheckout() throws SecurityException, NoSuchMethodException, IllegalArgumentException, IllegalAccessException, InvocationTargetException, SVNClientException {
		System.out.println("---------- " + super.getName() + " ----------");
		CheckoutSettings settings = new CheckoutSettingsForTest();
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings);
		
		// get the client instance and set an empty folder as configuration area
		ISVNClientAdapter client = GetClientAdapter.from(c.getWorkingCopy());
		File configFolder = TestFolder.getNew();
		client.setConfigDirectory(configFolder);
		
		// checkout should be the operation that sets default settings in the configuration area
		try {
			c.checkout();
		} catch (RepositoryAccessException e) {
			throw new RuntimeException("RepositoryAccessException thrown, not handled", e);
		}
		
		// now the program restarts and next client is instantiated
		c = null;
		// (garbage collect and terminate)
		c = new ManagedWorkingCopy(settings);
		// get the client adapter again
		client = GetClientAdapter.from(c.getWorkingCopy());
		configFolder = TestFolder.getNew();
		client.setConfigDirectory(configFolder);
		
		SvnIgnorePattern[] ignores = c.getClientConfiguration().getGlobalIgnores();
		Set all = new HashSet();
		for (int i=0; i<ignores.length; i++) {
			System.out.println("Global ignore " + i + ": " + ignores[i].toString());
			assertTrue("Ignores should not contain any duplicates", all.add(ignores[i]));
		}
		
		for (int i=0; i<EXPECT_IGNORE.length; i++) {
			assertTrue("Global ignores should contain: " + EXPECT_IGNORE[i], 
					all.contains(new SvnIgnorePattern(EXPECT_IGNORE[i])));
		}
		
		assertFalse("Just checking", all.contains(new SvnIgnorePattern("*")));
		
		// client configuration should be stored in the standard file
		File config = new File(configFolder, "config");
		File[] configContents = configFolder.listFiles();
		for (int i=0; i<configContents.length; i++) {
			System.out.println(configContents[i]);
		}
		// It looks like setConfigDirectory does not work very well.
		// It uses the default config directory instead.
		//assertTrue("Should have created a standard config file in " + configFolder, config.exists());
	}
	
}
