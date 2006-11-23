/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;
import java.lang.reflect.InvocationTargetException;
import java.net.MalformedURLException;

import junit.framework.TestCase;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.SVNUrl;

import se.repos.svn.RepositoryUrl;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.InvalidCredentialsException;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.SslCertificateHostMismatchException;
import se.repos.svn.checkout.SslCertificateNotTrustedException;
import se.repos.svn.checkout.client.GetClientAdapter;
import se.repos.svn.test.CheckoutSettingsForTest;
import se.repos.svn.test.TestFolder;

public class SSLIntegrationTest extends TestCase {

	/**
	 * All we need is an SSL host with a self-signed certificate that matches the host name.
	 * We don't need an account for this host to test certificate handling.
	 */
	public static final String HTTPS_URL = "https://www.repos.se/sweden/";
	
	public void testCheckout() throws SecurityException, NoSuchMethodException, IllegalArgumentException, IllegalAccessException, InvocationTargetException, SVNClientException {
		System.out.println("---------- " + super.getName() + " ----------");
		CheckoutSettings settings = new CheckoutSettingsForTest() {
			public RepositoryUrl getCheckoutUrl() {
				return new RepositoryUrl() {
					public SVNUrl getUrl() {
						try {
							return new SVNUrl(HTTPS_URL);
						} catch (MalformedURLException e) {
							fail(e.getMessage());
							return null;
						}
					}
				};
			}
		};
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings);
		
		// get the client instance and set an empty folder as configuration area
		ISVNClientAdapter client = GetClientAdapter.from(c.getWorkingCopy());
		File configFolder = TestFolder.getNew();
		client.setConfigDirectory(configFolder);
		
		// now try to do checkout
		try {
			c.checkout();
			fail("Should throw exception during authentication phase");
		} catch (SslCertificateNotTrustedException e) {
			fail("Should automatically accept (permanently) any certificate that matches the hostname");
		} catch (SslCertificateHostMismatchException e) {
			fail("Seems like the test host has an invalid certificate" + e.toString());
		} catch (InvalidCredentialsException e) {
			// expected, because if certificate is ok we get a login box
			//fail("Test error. The certificate was already accepted, if it was in the authentication area already this test does nothing.");
			//OK certificate is accepted, clear the authentication area manually and test again
		} catch (RepositoryAccessException e) {
			throw new RuntimeException("RepositoryAccessException thrown, not handled", e);
		}
		
		assertTrue("Shold have created an entry in the new config folder", configFolder.listFiles().length > 0);
		// should also see a log entry at INFO level
	}

}
