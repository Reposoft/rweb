/* $license_header$
 */
package se.repos.svn.config;

public class InvalidConfigurationChange extends RuntimeException {

	private static final long serialVersionUID = 1L;

	public InvalidConfigurationChange(String message) {
		super(message);
	}

}
