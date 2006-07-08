/* $license_header$
 */
package se.repos.issu.services;

import static org.junit.Assert.*;

import org.junit.Test;

import se.repos.issu.ContextForTesting;
import se.repos.issu.domain.Issue;

public class IssueServiceTest {

	private IssueService getIssueService() {
		return ContextForTesting.getBean("issueService", IssueService.class);
	}
	
	@Test
	public void testCreateAndOpen() {
		Issue issue = new Issue();
		issue.setName("new issue");
		getIssueService().create(issue);
		// check that the primary key is preserved
		assertNotNull("Should have parimary key set after creation", issue.getId());

		// try to open the stored object
		Issue persistentIssue = getIssueService().open(issue.getId());
		assertEquals(issue.getName(), persistentIssue.getName());
	}

}
