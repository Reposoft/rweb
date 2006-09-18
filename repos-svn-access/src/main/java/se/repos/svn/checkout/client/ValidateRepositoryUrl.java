/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.net.MalformedURLException;

import org.tigris.subversion.svnclientadapter.SVNUrl;

import se.repos.validation.ValidationResult;
import se.repos.validation.ValidationStrategy;

public class ValidateRepositoryUrl implements ValidationStrategy<String> {
	public ValidationResult validate(String url) {
		try {
			new SVNUrl(url);
		} catch (MalformedURLException e) {
			return ValidationResult.INVALID;
		}
		return ValidationResult.VALID;
	}
}
