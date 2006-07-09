/* $license_header$
 */
package se.repos.web;

import se.repos.web.contents.LoggingResourceFinder;
import se.repos.web.contents.LoggingResourceStreamLocator;
import se.repos.web.contents.RepositoryPages;
import se.repos.web.contents.RepositoryResourceStreamLocator;
import se.repos.web.statuspages.HelloWorld;
import se.repos.web.statuspages.Status;
import wicket.protocol.http.WebApplication;

public class CMS extends WebApplication {

	public static final Class ANY_PAGE = RepositoryPages.class;
	
	public CMS() {
	}
	
	@Override
	public Class getHomePage() {
		return ANY_PAGE;
	}

	@Override
	protected void init() {
		getResourceSettings().setResourceFinder(new LoggingResourceFinder(
		getResourceSettings().getResourceFinder()));
		getResourceSettings().setResourceStreamLocator(new LoggingResourceStreamLocator(
				new RepositoryResourceStreamLocator(
						getResourceSettings().getResourceStreamLocator())));
	}
	
}
