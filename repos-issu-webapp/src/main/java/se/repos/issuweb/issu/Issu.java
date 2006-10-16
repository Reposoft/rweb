/* $license_header$
 */
package se.repos.issuweb.issu;

import se.repos.issu.domain.Issue;
import se.repos.issu.services.IssueService;
import wicket.markup.html.WebPage;
import wicket.markup.html.basic.Label;
import wicket.spring.injection.annot.SpringBean;

public class Issu extends WebPage {

	private static final long serialVersionUID = 1L;

	@SpringBean private IssueService issueService;
	
	public Issu(long id) {
		super();
	
		Issue issue = issueService.open(id);
		add(new Label("name", issue.getName()));
		add(new Label("id", Long.toString(issue.getId())));
	}
	
	
}
