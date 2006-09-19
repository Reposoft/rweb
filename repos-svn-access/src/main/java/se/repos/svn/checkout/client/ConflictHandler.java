/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

import se.repos.svn.RepositoryUrl;
import se.repos.svn.checkout.ConflictInformation;

/**
 * Prepares a conflicting file so the conflict can be resolved by the user.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface ConflictHandler {
	
	/**
	 * Converts a reported conflict to a set of file references.
	 * Implementations of this interface may chose to modify, move or delete local files
	 * to suite a particular client application.
	 * @param path Absolute path, the original location of the file before the conflict
	 * @param fileUrl The online address for the file
	 * @return files
	 */
	ConflictInformation handleConflictingFile(File path, RepositoryUrl fileUrl);
	
}
