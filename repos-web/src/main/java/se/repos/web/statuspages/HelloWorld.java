/* $license_header$
 */
package se.repos.web.statuspages;

import wicket.markup.html.WebPage;
import wicket.markup.html.basic.Label;
import wicket.markup.html.link.BookmarkablePageLink;

public class HelloWorld extends WebPage
{
    private static final long serialVersionUID = 1L;

	public HelloWorld()
    {
        add(new Label("message", "Hello World!"));
        add(new BookmarkablePageLink("linkStatus", Status.class));
    }
}
