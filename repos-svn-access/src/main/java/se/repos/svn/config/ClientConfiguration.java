/* $license_header$
 */
package se.repos.svn.config;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.SvnProxySettings;

/**
 * Local subversion client configuration, such as ignre patterns and proxy settings.
 * 
 * Abstraction for the standard SVN
 * <a href="http://svnbook.red-bean.com/nightly/en/svn.advanced.html#svn.advanced.confarea">Runtime configuration area</a>.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface ClientConfiguration {

	/**
	 * Adds a global ignore pattern if it does not already exist.
	 * @param pattern To be added
	 */
	void addGlobalIgnore(SvnIgnorePattern pattern);
	
	/**
	 * Reads the list of ignores and isolates each pattern.
	 * @return All current global ignores
	 */
	SvnIgnorePattern[] getGlobalIgnores();
	
	/**
	 * Changes the current value of store-passwords
	 * @param authCache true to cache credentials
	 */
	void setStorePasswords(boolean authCache);
	
	/**
	 * Reads the current value of store-passwords
	 * @return true if credentials are cached
	 */
	boolean isStorePasswords();
	
	/**
	 * Changes the proxy settings for all hosts
	 * @param proxySettings new settings
	 */
	void setProxySettings(SvnProxySettings proxySettings);
	
	/**
	 * Reads the global proxy settings for the svn client
	 * @return current proxy settings
	 */
	SvnProxySettings getProxySettings();
	
}
