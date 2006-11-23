/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;
import java.io.IOException;
import java.util.HashSet;
import java.util.Set;

import junit.framework.TestCase;
import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ImmutableUserCredentials;
import se.repos.svn.checkout.InvalidCredentialsException;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.config.ClientConfiguration;
import se.repos.svn.config.ConfigurationStateException;
import se.repos.svn.test.CheckoutSettingsForTest;
import se.repos.svn.test.TestFolder;

public class DefaultClientConfigurationIntegrationTest extends TestCase {

	public static final String[] EXPECT_IGNORE = new String[] {
			"temp", "Temp", "TEMP", "~*", "Thumbs.db", ".DS_Store"
	};
	
	public void testCheckout() throws ConfigurationStateException, RepositoryAccessException {
		System.out.println("---------- " + super.getName() + " ----------");
		
		File configFolder = TestFolder.getNew();
		configFolder.delete(); // can't have an empty config folder
		
		CheckoutSettings settings = new CheckoutSettingsForTest();
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings, configFolder);
		
		// checkout should be the operation that sets default settings in the configuration area
		c.checkout();
		
		// now the program restarts and next client is instantiated
		c = null;
		// (garbage collect and terminate)
		c = new ManagedWorkingCopy(settings, configFolder);
		
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
		assertTrue("Should have created the custom configuraiton folder" + configFolder, configFolder.exists());
		File[] configContents = configFolder.listFiles();
		for (int i=0; i<configContents.length; i++) {
			System.out.println(configContents[i]);
		}
		assertTrue("Should have created a standard config file in " + configFolder, config.exists());
	}
	
	public void testEmptyConfigFolder() {
		System.out.println("---------- " + super.getName() + " ----------");
		
		File configFolder = TestFolder.getNew();
		CheckoutSettings settings = new CheckoutSettingsForTest();
		try {
			new ManagedWorkingCopy(settings, configFolder);
			fail("Should have failed because the folder does not contain configuration");
		} catch (ConfigurationStateException e) {
			// expected
		}
	}
	
	/**
	 * This is a tricky scenario:
	 * SVN client has not been used on this machine before. We want to do a first checkout
	 * of a working copy, but we don't want the client to store authentication
	 * (certificate or password).
	 * <p>
	 * Can't do checkout directly, because then there is no configuration, and the default is to store passwords.
	 * Can't change configuration, because this library does not know how to create configuration files.
	 * @throws ConfigurationStateException 
	 * @throws RepositoryAccessException 
	 * @throws ConflictException 
	 */
	public void testCheckout_ConfigNew_NoSaveAuth() throws ConfigurationStateException, RepositoryAccessException, ConflictException {
		System.out.println("---------- " + super.getName() + " ----------");
		
		File configFolder = TestFolder.getNew();
		configFolder.delete(); // can't have an empty config folder
		
		CheckoutSettings settings = new CheckoutSettingsForTest() {
			boolean first = true;
			public UserCredentials getLogin() {
				if (first) { first = false; return super.getLogin(); };
				return new ImmutableUserCredentials("test", "");
			}
		};
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings, configFolder);
		
		// cache passwords is default, so we want to change it before first checkout
		ClientConfiguration conf = c.getClientConfiguration();
		conf.setStorePasswords(false);
		
		c.checkout();
		
		// get a new client with a changed password
		assertEquals("Should have a new password in settings now", "", settings.getLogin().getPassword());
		c = new ManagedWorkingCopy(settings, configFolder);
		
		// now update should fail
		try {
			c.update();
			fail("Should have thrown InvalidCredentialsException, proving that it did not store the old password");
		} catch (InvalidCredentialsException e) {
			// expected
		}
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
