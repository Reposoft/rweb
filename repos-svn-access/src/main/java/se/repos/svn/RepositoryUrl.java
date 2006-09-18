/* Copyright 2006 Optime data Sweden
 */
package se.repos.svn;

import org.tigris.subversion.svnclientadapter.SVNUrl;

/**
 * The unique identifier for a repository resource such as a file or folder.
 * If it is a folder, it can be used to do checkout in svn clients.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface RepositoryUrl {

	/**
	 * @return immutable URL. Use .get() to get url, does not end with '/'
	 */
	SVNUrl getUrl();
	
}
