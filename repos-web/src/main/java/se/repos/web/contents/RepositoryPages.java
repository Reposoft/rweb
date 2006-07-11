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

	private static final long serialVersionUID = 1L;
	final Logger logger = LoggerFactory.getLogger(RepositoryPages.class);
	
	public RepositoryPages() {
	}
	
	public RepositoryPages(PageParameters pageParameters) {
	}

	/**
	 * Returns the path name as variation, 
	 * to allow resource loader to look up URL relative to this page.
	 * @return path with leading slash starting from context root
	 */
	//@Override
	public String getVariation() {
		return getWebRequestCycle().getWebRequest().getServletPath();
	}
	
	/**
	 * Not used, but intended to be called to check if a resource should
	 * be parsed with the MarkupParser.
	 * Should maybe be moved to the resource stream or locator.
	 * @param resourcePath
	 * @param pageParameters
	 * @return
	 */
	public boolean shouldDisableMarkupParser(String resourcePath, PageParameters pageParameters) {
		if (pageParameters != null && !pageParameters.isEmpty()) {
			return false;
		}
		// return extension!=html
		return false;
	}
}
