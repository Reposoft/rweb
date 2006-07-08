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
	
	@Test
	public void testSequenceIncrementIsOne() {
		Issue i1 = new Issue();
		i1.setName("new issue");
		getIssueService().create(i1);
		long id1 = i1.getId();
		
		Issue i2 = new Issue();
		i2.setName("new issue");
		getIssueService().create(i2);
		long id2 = i2.getId();
		
		assertEquals("Issue ID should increase with 1 for each issue", id1+1, id2);
	}

}
