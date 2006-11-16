/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.util.Arrays;
import java.util.List;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.config.ClientConfiguration;

/**
 * Verifies and updates the svn client configuration.
 * 
 * Global ignores:  <code>TEMP Temp temp #*# *~ .#* ~* Thumbs.db .DS_Store</code>
 * <p>
 * Users can create folders named <code>temp</code> anywhere in the working copy,
 * and contents of that folder will never be listed in versioning operations.
 * If they need a temporary file in a versioned folder, Repos recommends that
 * the name should start with "~". We could have added an ignore pattern "*.tmp",
 * but changing extension breaks associations, which is not good.
 * <p>
 * Every client should offer an opportunity to add a file, or folder, to its parent's
 * local ignores instead of adding it.
 * See {@link ReposWorkingCopy#getPropertiesForFolder(java.io.File)}.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class DefaultReposClientSettings implements ConfigureClient {

	private final Logger logger = LoggerFactory.getLogger(this.getClass());
	
	public DefaultReposClientSettings() {
	}

	public void update(ClientConfiguration runtimeConfiguration) {
		// subversion recommends
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("#*#"));
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("*~"));
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern(".#*"));
		// subversion says that ini file values are case insensitive, but ignore is not
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("TEMP"));
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("Temp"));
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("temp"));
		// MS office temp files
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("~*"));
		// Windows image thumbnails
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("Thumbs.db"));
		// OS X metadata
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern(".DS_Store"));
	}
	
	private void enforceGlobalIgnore(ClientConfiguration config, SvnIgnorePattern mustExist) {
		List existing = Arrays.asList(config.getGlobalIgnores());
		if (existing.contains(mustExist)) {
			logger.debug("Global ignores correctly contains: " + mustExist);
		} else {
			logger.warn("Adding the missing global ignore: " + mustExist);
			config.addGlobalIgnore(mustExist);
		}
	}
	
}
