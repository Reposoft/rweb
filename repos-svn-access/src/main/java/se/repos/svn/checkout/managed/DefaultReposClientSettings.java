/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.util.Arrays;
import java.util.List;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.config.ClientConfiguration;

public class DefaultReposClientSettings implements ConfigureClient {

	private final Logger logger = LoggerFactory.getLogger(this.getClass());
	
	public DefaultReposClientSettings() {
	}

	public void update(ClientConfiguration runtimeConfiguration) {
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("TEMP"));
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("Temp"));
		enforceGlobalIgnore(runtimeConfiguration, new SvnIgnorePattern("temp"));
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
