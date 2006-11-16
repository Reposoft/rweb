/* $license_header$
 */
package se.repos.svn;

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
	
	/**
	 * Port number to use if port has not been specified.
	 */
	public static final int NOPORT = 0;
	
	/**
	 * Host string to use if proxy host is not specified.
	 */
	public static final String NOHOST = "";
	
	/**
	 * The empty proxy settings to return if svn configuration is for no proxy use.
	 * Note that instances should still be compared with equals, not ==, because
	 * it is possible to create empty proxy settings without using this instance.
	 */
	public static final SvnProxySettings NOPROXY = 
		new SvnProxySettings(NOHOST, NOPORT);

	public SvnProxySettings(String host, int port) {
		if (host==null) {
			throw new RuntimeException("Proxy host 'null' not allowed. Use NOPROXY if proxy is not needed.");
		}
		this.host = host;
		if (port==NOPORT) {
			this.port = "";
		} else {
			this.port = Integer.toString(port);
		}
	}

	public String getHost() {
		return host;
	}

	public String getPort() {
		return port;
	}
	
	/**
	 * @return password, or null if no password is required
	 */
	public String getPassword() {
		return password;
	}

	/**
	 * @return username, or null if no username is required
	 */
	public String getUsername() {
		return username;
	}
	
	/**
	 * Sets the proxy password, only allowed if username is set
	 * @param password
	 */
	public void setPassword(String password) {
		if (username==null) throw new IllegalArgumentException("Must have username to set password");
		this.password = password;
	}

	public void setUsername(String username) {
		if (username!=null && username.length()==0) {
			throw new IllegalArgumentException("Empty username not allowed. Use null if username is not required.");
		}
		this.username = username;
	}
	
	public String toString() {
		StringBuffer sb = new StringBuffer();
		if (username != null) {
			sb.append(username).append(':');
			if (password != null) {
				for (int i=0; i<password.length(); i++) sb.append('*');
			}
			sb.append('@');
		}
		sb.append(host);
		if (port.length()>0) {
			sb.append(':');
			sb.append(port);
		}
		return sb.toString();
	}

	public boolean equals(Object obj) {
		SvnProxySettings o = (SvnProxySettings) obj;
		if (!host.equals(o.getHost())) return false;
		if (!port.equals(o.getPort())) return false;
		if (username == null) {
			if (o.getUsername() != null) return false;
		} else {
			if (!username.equals(o.getUsername())) return false;
		}
		if (password == null) {
			if (o.getPassword() != null) return false;
		} else {
			if (!password.equals(o.getPassword())) return false;
		}
		return true;
	}

	public int hashCode() {
		StringBuffer sb = new StringBuffer();
		if (username != null) {
			sb.append(username).append(':');
			if (password != null) sb.append(password);
			sb.append('@');
		}
		sb.append(host);
		sb.append(':');
		sb.append(port);
		return sb.toString().hashCode();
	}
	
}
