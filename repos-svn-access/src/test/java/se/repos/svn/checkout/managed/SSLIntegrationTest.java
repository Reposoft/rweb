/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;
import java.net.MalformedURLException;

import org.tigris.subversion.svnclientadapter.SVNUrl;

import se.repos.svn.RepositoryUrl;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.InvalidCredentialsException;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.SslCertificateHostMismatchException;
import se.repos.svn.checkout.SslCertificateNotTrustedException;
import se.repos.svn.test.CheckoutSettingsForTest;
import junit.framework.TestCase;

public class SSLIntegrationTest extends TestCase {

	/**
	 * All we need is an SSL host with a self-signed certificate that matches the host name.
	 * We don't need an account for this host to test certificate handling.
	 */
	public static final String HTTPS_REPOSITORY = "https://www.repos.se/sweden/";
	
	// test workingcopy path
	private File path;
	
	private ManagedWorkingCopy client;
	
	public void testCheckout() {
		System.out.println("---------- " + super.getName() + " ----------");
		CheckoutSettings settings = new CheckoutSettingsForTest() {
			public RepositoryUrl getCheckoutUrl() {
				return new RepositoryUrl() {
					public SVNUrl getUrl() {
						try {
							return new SVNUrl(HTTPS_REPOSITORY);
						} catch (MalformedURLException e) {
							fail(e.getMessage());
							return null;
						}
					}
				};
			}
		};
		path = settings.getWorkingCopyFolder();
		ManagedWorkingCopy c = new ManagedWorkingCopy(settings);
		
		// now try to do checkout
		try {
			c.checkout();
			fail("Should throw exception during authentication phase");
		} catch (SslCertificateNotTrustedException e) {
			fail("Should automatically accept (permanently) any certificate that matches the hostname");
		} catch (SslCertificateHostMismatchException e) {
			fail("Seems like the test host has an invalid certificate" + e.toString());
		} catch (InvalidCredentialsException e) {
			//fail("Test error. The certificate was already accepted, if it was in the authentication area already this test does nothing.");
			//OK certificate is accepted, clear the authentication area manually and test again
		} catch (RepositoryAccessException e) {
			throw new RuntimeException("RepositoryAccessException thrown, not handled", e);
		}
	}

}
