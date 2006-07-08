/* $license_header$
 */
package se.repos.issu.fixtures;

import se.repos.issu.ContextForTesting;
import se.repos.issu.domain.Issue;
import se.repos.issu.services.IssueService;
import fit.Fixture;

public class Issues extends Fixture {
	
	private long id;
	private String name = null;
	
	private Issue openIssue = null;
	
	public void newIssue() {
		Issue issue = new Issue();
		issue.setName(name);
		
		IssueService issueService = getIssueService();
		issueService.create(issue);
		System.out.println("ID of the new issue is " + issue.getId());
		openIssue = issue;
	}

	private IssueService getIssueService() {
		return ContextForTesting.getBean("issueService", IssueService.class);
	}
	
	/**
	 * @return Id of open issue
	 */
	public long id() {
		return openIssue.getId();
	}
	
	public void id(long id) {
		this.id = id;
	}
	
	/**
	 * @return name of open issue
	 */
	public String name() {
		return openIssue.getName();
	}
	
	public void name(String name) {
		this.name = name;
	}
	
	// operations ///////////
	
	public void show() throws Exception {
		openIssue = getIssueService().open(id);
		if (openIssue == null) {
			
			throw new Exception("Could not open issue with ID " + id);
		}
	}
}
