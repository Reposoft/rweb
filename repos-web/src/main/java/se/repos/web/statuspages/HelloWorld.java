/* $license_header$
 */
package se.repos.web.statuspages;

import se.repos.web.CMS;
import wicket.markup.html.WebPage;
import wicket.markup.html.basic.Label;
import wicket.markup.html.link.BookmarkablePageLink;
import wicket.markup.html.link.PageLink;

public class HelloWorld extends WebPage
{
    private static final long serialVersionUID = 1L;

	public HelloWorld()
    {
        add(new Label("message", "Hello World!"));
        add(new BookmarkablePageLink("linkStatus", Status.class));
        add(new PageLink("linkContents", CMS.ANY_PAGE));
    }
}
