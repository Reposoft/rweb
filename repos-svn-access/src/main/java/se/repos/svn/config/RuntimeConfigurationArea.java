/* $license_header$
 */
package se.repos.svn.config;

import java.io.File;
import java.lang.reflect.Method;

import org.tigris.subversion.svnclientadapter.AbstractClientAdapter;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.SvnProxySettings;

/**
 * Abstraction for a the subversion client configuration area.
 * 
 * Does not support windows registry.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class RuntimeConfigurationArea implements ClientConfiguration {
	
	/**
	 * Reads configuration from the default subversion user folder
	 */
	public RuntimeConfigurationArea() {
		
	}
	
	/**
	 * 
	 * @param configFolder The SVN client's runtime configuration area.
	 */
	public RuntimeConfigurationArea(File configFolder) {
		
	}

	public void addGlobalIgnore(SvnIgnorePattern pattern) {
		if (true) {
			throw new UnsupportedOperationException("Method RuntimeConfigurationArea#addGlobalIgnore not implemented yet");
		}
		
	}

	public SvnIgnorePattern[] getGlobalIgnores() {
		if (true) {
			throw new UnsupportedOperationException("Method RuntimeConfigurationArea#getGlobalIgnores not implemented yet");
		}
		return null;
	}

	public SvnProxySettings getProxySettings() {
		if (true) {
			throw new UnsupportedOperationException("Method RuntimeConfigurationArea#getProxySettings not implemented yet");
		}
		return null;
	}

	public boolean isStorePasswords() {
		if (true) {
			throw new UnsupportedOperationException("Method RuntimeConfigurationArea#getStorePasswords not implemented yet");
		}
		return false;
	}

	public void setProxySettings(SvnProxySettings proxySettings) {
		if (true) {
			throw new UnsupportedOperationException("Method RuntimeConfigurationArea#setProxySettings not implemented yet");
		}
		
	}

	public void setStorePasswords(boolean authCache) {
		if (true) {
			throw new UnsupportedOperationException("Method RuntimeConfigurationArea#setStorePasswords not implemented yet");
		}
		
	}
	
	/**
	 * ISVNClientAdapter has no method that can report configuration folder, so the logic for that is here.
	 * @return the default SVN client configuration area for the user
	 */
	public static File getConfigFolder() {
		File f = new File(getAppdataFolderForUser(), getSubversionConfigFolderName());
		if (!f.exists() || !f.isDirectory()) throw new RuntimeException(f + " is not a valid configuration folder");
		// TODO what to do if they don't exist?
		return f;
	}
	
	/**
	 * Gets the folder containing the subversion configuration folder.
	 * 
	 * For non-windows, the user.home folder is used to store configuration folders.
	 * 
	 * In Windows with Java 1.5, System.getenv and APPDATA environment can be used.
	 * For earlier JVMs, a qualified guess is made based on the user.home system property.
	 * If the property can not be found, an error message suggesting an upgrade to Java 1.5 is thrown.
	 * 
	 * @return the current user's application data
	 * @throws RuntimeException if the folder can not be found
	 */
	private static File getAppdataFolderForUser() {
		if (!RuntimeConfigurationArea.isWindows()) {
			return new File(System.getProperty("user.home"));
		}
		try {
	        Method getenv = System.class.getMethod("getenv", new Class[] {String.class});
	        if (getenv != null) {
	            Object appdata = getenv.invoke(null, new Object[] {"APPDATA"});
	            if (appdata != null) {
	                return new File(appdata.toString());
	            }
	        }
		} catch (Exception e) { }
		File guess = new File(System.getProperty("user.home"), "Application Data");
		if (!guess.exists()) throw new RuntimeException("Could not locate application data folder. It might help to upgrade to Java 5.");
		return guess;
	}
	
	/**
	 * Guesses the folder name inside the application data folder for the runtime configuration area.
	 * Guess according to {@link http://svnbook.red-bean.com/nightly/en/svn.advanced.html#svn.advanced.confarea}.
	 * @return folder name
	 */
	private static String getSubversionConfigFolderName() {
		if (isWindows()) {
			return "Subversion";
		} else {
			return ".subversion";
		}
	}
	
	/**
	 * @return true if running on Windows operating system, false for all other
	 */
	private static boolean isWindows() {
		return AbstractClientAdapter.isOsWindows();
	}
	
}
