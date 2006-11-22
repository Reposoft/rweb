/* $license_header$
 */
package se.repos.svn.config;

public class ConfigurationStateException extends Exception {

	private static final long serialVersionUID = 1L;

	public ConfigurationStateException(String message) {
		super(message);
	}
	
	public ConfigurationStateException(String message, Throwable e) {
		super(message, e);
	}

}
