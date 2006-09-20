/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.net.MalformedURLException;

import org.tigris.subversion.svnclientadapter.SVNUrl;

import se.repos.svn.RepositoryUrl;
import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.CheckoutSettings;

public abstract class AbstractCheckoutSettings implements CheckoutSettings {

	private RepositoryUrl repositoryUrl;
	private File workingCopyDirectory;

	/**
	 * 
	 * @param url
	 * @param workingCopyAbsolutePath 
	 * @throws IllegalArgumentException if the url or path is invalid
	 */
	public AbstractCheckoutSettings(String url, String workingCopyAbsolutePath) throws IllegalArgumentException {
		this(url, convertToFile(workingCopyAbsolutePath));
	}
	
	public AbstractCheckoutSettings(String url, File workingCopyAbsolutePath) throws IllegalArgumentException {
		this(new Url(url), workingCopyAbsolutePath);
	}

	public AbstractCheckoutSettings(RepositoryUrl url, File workingCopyAbsolutePath) throws IllegalArgumentException {
		validateWorkingCopyPath(workingCopyAbsolutePath);
		this.repositoryUrl = url;
		this.workingCopyDirectory = workingCopyAbsolutePath;
	}	
	
	public void validateWorkingCopyPath(File path) throws InvalidWorkingCopyPath {
		if (path == null) throw new InvalidWorkingCopyPath("is empty", path);
		if (!path.isAbsolute()) throw new InvalidWorkingCopyPath("is not absolute", path);
		if (!path.exists()) throw new InvalidWorkingCopyPath("does not exist", path);
		if (!path.isDirectory()) throw new InvalidWorkingCopyPath("is not a directory", path);
		if (!path.canRead()) throw new InvalidWorkingCopyPath("is not readable", path);
		if (!path.canWrite()) throw new InvalidWorkingCopyPath("is not writable", path);
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
		if (!path.getAbsolutePath().startsWith(getWorkingCopyDirectory().getAbsolutePath())) {
			throw new IllegalArgumentException("The path " + path + " is not under working copy directory " + getWorkingCopyDirectory());
		}
		return path.getAbsolutePath().substring(getWorkingCopyDirectory().getAbsolutePath().length() + 1);
	}
	
	private static File convertToFile(String workingCopyAbsolutePath) {
		return new File(workingCopyAbsolutePath);
	}
	
	private static class Url implements RepositoryUrl {
		private SVNUrl url;
		Url(String url) {
			try {
				this.url = new SVNUrl(url);
			} catch (MalformedURLException e) {
				throw new IllegalArgumentException("Invalid repository URL: " + url, e);
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

	class InvalidWorkingCopyPath extends IllegalArgumentException {
		private static final long serialVersionUID = 1L;
		private File path;
		public InvalidWorkingCopyPath(String message, File path) {
			super(message);
			this.path = path;
		}
		public String getMessage() {
			return super.getMessage() + ": " + path;
		}
	}
	
}
