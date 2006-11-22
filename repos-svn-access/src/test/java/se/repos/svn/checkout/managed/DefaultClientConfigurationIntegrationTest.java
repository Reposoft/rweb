/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;
import java.io.IOException;
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
	
	public void testCheckout() throws SecurityException, NoSuchMethodException, IllegalArgumentException, IllegalAccessException, InvocationTargetException, SVNClientException, RepositoryAccessException {
		System.out.println("---------- " + super.getName() + " ----------");
		
		CheckoutSettings settings = new CheckoutSettingsForTest();
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings);
		
		// get the client instance and set an empty folder as configuration area
		ISVNClientAdapter client = GetClientAdapter.from(c.getWorkingCopy());
		File configFolder = TestFolder.getNew();
		client.setConfigDirectory(configFolder);
		
		// checkout should be the operation that sets default settings in the configuration area
		c.checkout();
		
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
	
	/**
	 * This is a tricky scenario:
	 * SVN client has not been used on this machine before. We want to do a first checkout
	 * of a working copy, but we don't want the client to store authentication
	 * (certificate or password).
	 * <p>
	 * Can't do checkout directly, because then there is no configuration, and the default is to store passwords.
	 * Can't change configuration, because this library does not know how to create configuration files.
	 */
	public void testCheckout_ConfigNew_NoSaveAuth() {
		
		
	}
	
	public void testUnversionedContentsWithIgnores() throws RepositoryAccessException, IOException {
		System.out.println("---------- " + super.getName() + " ----------");
		CheckoutSettings settings = new CheckoutSettingsForTest();
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings);
		
		// checkout should be the operation that sets default settings in the configuration area
		c.checkout();
		
		// no contents
		File path = settings.getWorkingCopyFolder();
		assertEquals("Working copy should have no new contents yet", 0, c.getUnversionedContents(path).length);
		
		File temp = new File(path, "temp");
		temp.mkdir();
		File tempfile = new File(temp, "f.txt");
		tempfile.createNewFile();
		File thumbs = new File(path, "Thumbs.db");
		thumbs.createNewFile();
		File dsstore = new File(path, ".DS_Store");
		dsstore.mkdir();
		File officetemp = new File(path, "~a.doc");
		officetemp.createNewFile();
		// and some things that should not be ignored
		File folder = new File(path, "folder");
		folder.mkdir();
		File file = new File(folder, "f.txt");
		file.createNewFile();
		File filetemp = new File(folder, "TEMP");
		filetemp.createNewFile();
		File fileb = new File(folder, "mine.b");
		fileb.createNewFile();

		File[] unversioned = c.getUnversionedContents(path);
		assertEquals("The folder named 'folder' should not be ignored", 1, unversioned.length);
		// add the folder and check that unversioned contents are found
		c.add(folder);
		unversioned = c.getUnversionedContents(path);
		assertEquals(2, unversioned.length);
		assertEquals("f.txt", unversioned[0].getName());
		assertEquals("mine.b", unversioned[1].getName());
		// add all contents of folder recursively, and check that global ignore works
		c.getPropertiesForFolder(folder).setIgnore(new SvnIgnorePattern("*.b"));
		c.addNew(folder);
		assertTrue("Should have added the not ignored f.txt", c.isVersioned(file));
		assertFalse("Should not have added globally ignored file", c.isVersioned(filetemp));
		assertFalse("Should not have added locally ignored file", c.isVersioned(fileb));
		assertEquals("No unversioned contents left", 0, c.getUnversionedContents(path).length);
	}
	
}
