/* $license_header$
 */
package se.repos.svn.config;

/**
 * Represents proxy settings with mandatory host and port and optional login.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class SvnProxySettings {

	private String host;
	private String port;
	private String username;
	private String password;

	public SvnProxySettings(String host, int port) {
		this.host = host;
		this.port = Integer.toString(port);
	}

	public String getHost() {
		return host;
	}

	public String getPort() {
		return port;
	}
	
	public String getPassword() {
		return password;
	}

	public void setPassword(String password) {
		this.password = password;
	}

	public String getUsername() {
		return username;
	}

	public void setUsername(String username) {
		this.username = username;
	}
	
}
