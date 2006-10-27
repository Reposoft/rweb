/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

/**
 * Encapsulates the functionality for setting and reading file or folder properties.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface VersionedProperties {

	/**
	 * Returns the versioned resource that this instance represents.
	 * @return the file or folder that holds these properties
	 */
	public File getPath();
	
	/**
	 * Adds local svn:ignore property value to the list of ignores for a folder.
	 * 
	 * @param parent the folder to set the ignore property on
	 * @param ignorePattern the names to ignore, like '*.wbk' or 'tempfile.txt'
	 */
	public void setIgnore(String ignorePattern);

	/**
	 * Adds local svn:ignore property value to the list of ignores for a folder.
	 * 
	 * @param parent the folder to set the ignore property on
	 * @param ignorePattern the names to ignore, like '*.wbk' or 'tempfile.txt'
	 */
	public void setIgnore(File ignoreChild);
	
}
