/* $license_header$
 */
package se.repos.svn.config;

import java.io.File;

import org.tmatesoft.svn.core.internal.wc.SVNFileUtil;

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

	public boolean getStorePasswords() {
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
	
	
}
