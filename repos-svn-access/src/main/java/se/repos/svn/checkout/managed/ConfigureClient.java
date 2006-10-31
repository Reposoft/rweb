/* $license_header$
 */
package se.repos.svn.checkout.managed;

import se.repos.svn.config.ClientConfiguration;

public interface ConfigureClient {

	/**
	 * Sets required values in the runtime configuration area.
	 * @param runtimeConfiguration Current client configuration.
	 */
	public void update(ClientConfiguration runtimeConfiguration);
	
}
