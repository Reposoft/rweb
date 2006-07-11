/* $license_header$
 */
package se.repos.web;

import se.repos.web.contents.LoggingResourceStreamLocator;
import se.repos.web.contents.RepositoryPages;
import se.repos.web.contents.RepositoryResourceStreamLocator;
import wicket.protocol.http.WebApplication;

public class CMS extends WebApplication {

	private static final Class CONTENTS_PAGE = RepositoryPages.class;
	
	public CMS() {
	}
	
	//@Override
	public Class getHomePage() {
		return CONTENTS_PAGE;
	}

	//@Override
	protected void init() {
		//getResourceSettings().setResourceFinder(new LoggingResourceFinder(getResourceSettings().getResourceFinder()));
		getResourceSettings().setResourceStreamLocator(new LoggingResourceStreamLocator(
				new RepositoryResourceStreamLocator(
						getResourceSettings().getResourceStreamLocator())));
	}
	
}
