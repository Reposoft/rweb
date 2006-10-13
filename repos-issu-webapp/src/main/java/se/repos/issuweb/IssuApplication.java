/* $license_header$
 */
package se.repos.issuweb;

import se.repos.issuweb.start.Start;
import wicket.protocol.http.WebApplication;

public class IssuApplication extends WebApplication {

	@Override
	public Class getHomePage() {
		return Start.class;
	}

}
