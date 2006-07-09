/* $license_header$
 */
package se.repos.web.contents;

import java.net.MalformedURLException;
import java.net.URL;
import java.util.Locale;

import wicket.util.resource.IResourceStream;
import wicket.util.resource.UrlResourceStream;
import wicket.util.resource.locator.IResourceStreamLocator;

public class RepositoryResourceStreamLocator implements IResourceStreamLocator {

	String repositoryBaseUrl = "http://svn.collab.net/repos/svn/tags/1.3.2/www";
	
	IResourceStreamLocator parent;
	
	public RepositoryResourceStreamLocator(IResourceStreamLocator parent) {
		this.parent = parent;
	}
	
	/**
	 * See interface
	 * @return null if page not found
	 */
	public IResourceStream locate(Class clazz, String path, String style,
			Locale locale, String extension) {
		// handle normal wicket resources
		if (!RepositoryPages.class.equals(clazz)) {
			return parent.locate(clazz, path, style, locale, extension);
		}
		// locate from repository
		return new UrlResourceStream(getURL(style));
	}

	private URL getURL(String style) {
		String url = repositoryBaseUrl + style;
		try {
			return new URL(url);
		} catch (MalformedURLException e) {
			throw new RuntimeException("Could not make URL " + url, e);
		}
	}

}
