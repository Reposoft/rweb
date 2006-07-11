/* $license_header$
 */
package se.repos.web.statuspages;

import wicket.markup.html.WebPage;
import wicket.markup.html.basic.Label;
import wicket.markup.html.link.PageLink;

public class Status extends WebPage {

	private static final long serialVersionUID = 1L;

	public Status() {
		add(new Label("message", "Up and running since a while"));
		add(new PageLink("linkStartpage", HelloWorld.class));
	}
}
