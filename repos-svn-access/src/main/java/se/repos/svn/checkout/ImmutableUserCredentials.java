/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.Serializable;

import se.repos.svn.UserCredentials;

/**
 * Basic UserCredentials implementation.
 * Immutable, to be passed freely around the application for authenticating the current user.
 * @version $Id$
 */
public class ImmutableUserCredentials implements UserCredentials, Serializable {

	private static final long serialVersionUID = 1L;
	String username;
	String password;
	
	public ImmutableUserCredentials(String username, String password) {
		this.username = username;
		this.password = password;
	}
	public String getUsername() {
		return username;
	}
	public String getPassword() {
		return password;
	}
	public String toString() {
		return getUsername() + ':' + password.replaceAll(".", "*");
	}
}
