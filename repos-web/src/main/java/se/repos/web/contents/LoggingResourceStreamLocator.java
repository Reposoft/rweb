/* $license_header$
 */
package se.repos.web.contents;

import java.util.Locale;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import wicket.util.resource.IResourceStream;
import wicket.util.resource.locator.IResourceStreamLocator;

/**
 * Decorates a ResourceStreamLocator with logging of all locate attempts.
 *
 * @author solsson
 * @since 2006 jul 9
 * @version $Id$
 */
public class LoggingResourceStreamLocator implements IResourceStreamLocator {

	final Logger logger = LoggerFactory.getLogger(LoggingResourceStreamLocator.class);
	private IResourceStreamLocator resourceStreamLocator;
	
	public LoggingResourceStreamLocator(IResourceStreamLocator realLocator) {
		this.resourceStreamLocator = realLocator;
	}
	
	public IResourceStream locate(Class clazz, String path, String style,
			Locale locale, String extension) {
		logger.info("ResourceStreamLocator.locat({}, {}, {}, {}, {})", new Object[]{clazz, path, style, locale, extension});
		return resourceStreamLocator.locate(clazz, path, style, locale, extension);
	}

}
