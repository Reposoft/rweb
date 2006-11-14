/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.tigris.subversion.svnclientadapter.SVNClientException;

/**
 * Thrown if username does not exist or if username and password does not match.
 *
 * Javahl reports:<pre>
Authorization failed
svn: PROPFIND request failed on '/sweden'
svn: PROPFIND of '/sweden': authorization failed (https://www.repos.se)
</pre>
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class InvalidCredentialsException extends RepositoryAccessException {

	private static final Pattern MATCH = Pattern.compile("^.*svn:.*authorization failed\\s+\\(([^\\)]+).*$", Pattern.DOTALL);
	
	static void identify(SVNClientException e) throws InvalidCredentialsException {
		Matcher matcher = MATCH.matcher(e.getMessage());
		if (matcher.matches()) {
			throw new InvalidCredentialsException(matcher.group(1));
		}
	}
	
	private static final long serialVersionUID = 1L;
	
	private String realm;
	
	protected InvalidCredentialsException(String realm) {
		super("Username or password not accepted for realm " + realm);
		this.realm = realm;
	}

	protected String getRealm() {
		return realm;
	}

}
