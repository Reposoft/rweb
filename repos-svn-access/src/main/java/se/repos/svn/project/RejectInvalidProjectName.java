/* $license_header$
 */
package se.repos.svn.project;

import se.repos.validation.ValidationRejectStrategy;

public class RejectInvalidProjectName extends ValidationRejectStrategy<String> {

	@Override
	public boolean rejects(String name) {
		return name.contains("/");
	}

}
