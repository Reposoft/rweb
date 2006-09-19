/* $license_header$
 */
package se.repos.svn.checkout.simple;

import junit.framework.TestCase;

public class ProjectCheckoutSettingsTest extends TestCase {

	public void testProjectNameRule() {
		assertTrue(ProjectCheckoutSettings.PROJECT_NAME_RULE.matcher("aproject").matches());
		assertTrue(ProjectCheckoutSettings.PROJECT_NAME_RULE.matcher("a2").matches());
		assertTrue(ProjectCheckoutSettings.PROJECT_NAME_RULE.matcher("a").matches());
		
		// not decided in common repos.se if spaces is allowed
		assertFalse(ProjectCheckoutSettings.PROJECT_NAME_RULE.matcher("a project").matches());
		
		assertFalse(ProjectCheckoutSettings.PROJECT_NAME_RULE.matcher("2poject").matches());
		assertFalse(ProjectCheckoutSettings.PROJECT_NAME_RULE.matcher("/project").matches());
		assertFalse(ProjectCheckoutSettings.PROJECT_NAME_RULE.matcher("project/").matches());
	}
}
