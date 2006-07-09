/* $license_header$
 */
package se.repos.web.contents;

import java.net.URL;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import wicket.util.file.IResourceFinder;

/**
 * Decorates a ResourceFinder with logging of all lookup attempts.
 *
 * @author solsson
 * @since 2006 jul 9
 * @version $Id$
 */
public class LoggingResourceFinder implements IResourceFinder {

	final Logger logger = LoggerFactory.getLogger(LoggingResourceFinder.class);
	
	private IResourceFinder resourceFinder;

	public LoggingResourceFinder(IResourceFinder realFinder) {
		this.resourceFinder = realFinder;
	}

	public URL find(String pathname) {
		logger.info("ResourceFinder find({})", pathname);
		return resourceFinder.find(pathname);
	}
	
}
