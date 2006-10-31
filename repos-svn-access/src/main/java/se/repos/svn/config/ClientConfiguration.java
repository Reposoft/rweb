/* $license_header$
 */
package se.repos.svn.config;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.SvnProxySettings;

/**
 * Local subversion client configuration, such as ignre patterns and proxy settings.
 * 
 * Abstraction for the standard SVN
 * {@link http://svnbook.red-bean.com/nightly/en/svn.advanced.html#svn.advanced.confarea Runtime configuration area}.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface ClientConfiguration {

	void addGlobalIgnore(SvnIgnorePattern pattern);
	
	SvnIgnorePattern[] getGlobalIgnores();
	
	void setStorePasswords(boolean authCache);
	
	boolean isStorePasswords();
	
	// -- network configuration, or should this be in the authentication information --
	
	void setProxySettings(SvnProxySettings proxySettings);
	
	SvnProxySettings getProxySettings();
	
}
