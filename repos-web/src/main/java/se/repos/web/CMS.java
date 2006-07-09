/* $license_header$
 */
package se.repos.web;

import se.repos.web.statuspages.HelloWorld;
import wicket.protocol.http.WebApplication;

public class CMS extends WebApplication {

	@Override
	public Class getHomePage() {
		return HelloWorld.class;
	}

}
