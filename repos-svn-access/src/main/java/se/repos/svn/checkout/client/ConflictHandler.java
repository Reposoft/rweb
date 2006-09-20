/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

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
	 * @return files
	 */
	ConflictInformation handleConflictingFile(File path);
	
	/**
	 * Does for example cleanup when the target file has been manually merged
	 * and the conflict marked resolved in the working copy.
	 * @param conflictInformation The instance that was returned from this handler
	 */
	void afterConflictResolved(ConflictInformation conflictInformation);
	
}
