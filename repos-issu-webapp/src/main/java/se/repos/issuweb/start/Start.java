/* $license_header$
 */
package se.repos.issuweb.start;

import wicket.markup.html.WebPage;
import wicket.markup.html.basic.Label;

public class Start extends WebPage {

	private static final long serialVersionUID = 1L;

	public Start() {
		add(new Label("message", "Lägg en issu"));
	}
}
