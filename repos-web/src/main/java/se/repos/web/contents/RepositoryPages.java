/* $license_header$
 */
package se.repos.web.contents;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import wicket.PageParameters;
import wicket.markup.html.WebPage;
import wicket.protocol.http.WebRequest;

/**
 * Any time a request goes to this page the resource should be read from the repository.
 * 
 * The relative URL of the resource is decided by {@see {@link WebRequest#getServletPath()}}
 * and returned as {@link #getVariation()}.
 *
 * @author solsson
 * @since 2006 jul 9
 * @version $Id$
 */
public final class RepositoryPages extends WebPage {

	final Logger logger = LoggerFactory.getLogger(RepositoryPages.class);
	
	public RepositoryPages(PageParameters pageParameters) {
		//path = getWebRequestCycle().getWebRequest().getServletPath();
		//logger.info("Showing page {} with parameters {}", path, pageParameters);
	}

	/**
	 * Returns the path name as variation, 
	 * to allow resource loader to look up URL relative to this page.
	 */
	@Override
	public String getVariation() {
		return getWebRequestCycle().getWebRequest().getServletPath();
	}
	
	
}
