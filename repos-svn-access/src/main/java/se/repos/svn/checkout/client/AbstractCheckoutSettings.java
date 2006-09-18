/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.net.MalformedURLException;

import org.tigris.subversion.svnclientadapter.SVNUrl;

import se.repos.svn.RepositoryUrl;
import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.validation.Validation;
import se.repos.validation.ValidationRule;

public abstract class AbstractCheckoutSettings implements CheckoutSettings {

	private RepositoryUrl repositoryUrl;
	private File workingCopyDirectory;

	public AbstractCheckoutSettings(String url, String workingCopyAbsolutePath) {
		this(url, convertToFile(workingCopyAbsolutePath));
	}
	
	public AbstractCheckoutSettings(String url, File workingCopyAbsolutePath) {
		this(new Url(url), workingCopyAbsolutePath);
	}

	public AbstractCheckoutSettings(RepositoryUrl url, File workingCopyAbsolutePath) {
		this.repositoryUrl = url;
		this.workingCopyDirectory = workingCopyAbsolutePath;
	}	
	
	public abstract UserCredentials getLogin();
	
	public RepositoryUrl getCheckoutUrl() {
		return this.repositoryUrl;
	}

	public File getWorkingCopyDirectory() {
		return this.workingCopyDirectory;
	}
	
	public String toString() {
		return "svn checkout " + getLogin() + ' ' + getCheckoutUrl() + ' ' + getWorkingCopyDirectory();
	}
	
	public String toRelative(File path) {
		if (!path.isAbsolute()) {
			return path.getPath();
		}
		return null;
	}
	
	private static File convertToFile(String workingCopyAbsolutePath) {
		return new File(workingCopyAbsolutePath);
	}
	
	private static class Url implements RepositoryUrl {
		static final ValidationRule<String> URL_VALIDATOR = Validation.rule(ValidateRepositoryUrl.class);
		private SVNUrl url;
		Url(String url) {
			URL_VALIDATOR.validate(url); // should guarantee that we don't get a MalformedURLException
			try {
				this.url = new SVNUrl(url);
			} catch (MalformedURLException e) {
				throw new RuntimeException("URL was not properly validated as it should have been automatically", e);
			}
		}
		public SVNUrl getUrl() {
			return url;
		}
		public String toString() {
			return getUrl().toString();
		}
		public boolean equals(Object url) {
			return this.url.toString().equals(url.toString());
		}
	}

}
