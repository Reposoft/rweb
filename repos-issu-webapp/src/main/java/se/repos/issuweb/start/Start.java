/* $license_header$
 */
package se.repos.issuweb.start;

import se.repos.issu.domain.Issue;
import se.repos.issu.services.IssueService;
import se.repos.issuweb.issu.Issu;
import wicket.markup.html.WebPage;
import wicket.markup.html.basic.Label;
import wicket.markup.html.link.PageLink;
import wicket.spring.injection.annot.SpringBean;

public class Start extends WebPage {

	private static final long serialVersionUID = 1L;

	@SpringBean private IssueService issueService;
	
	public Start() {
		Issue issue = new Issue();
		issue.setName("repos-issu-webapp should be developed shortly");
		issueService.create(issue);

		add(new Label("message", "Created test issue with number: " + issue.getId()));
		
		add(new PageLink("testissue", new Issu(issue.getId())));
	}
	
	

	
}
