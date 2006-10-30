/* $license_header$
 */
package se.repos.svn.checkout;

import se.repos.svn.SvnIgnorePattern;

public interface VersionedFolderProperties extends VersionedProperties {

	/**
	 * Adds local svn:ignore property value to the list of ignores for a folder.
	 * 
	 * @param parent the folder to set the ignore property on
	 * @param ignorePattern the names to ignore, like '*.wbk' or 'tempfile.txt'
	 */
	//public void setIgnore(String ignorePattern);

	/**
	 * Adds local svn:ignore property value to the list of ignores for a folder.
	 * 
	 * @param parent the folder to set the ignore property on
	 * @param ignorePattern the names to ignore, like '*.wbk' or 'tempfile.txt'
	 */
	//public void setIgnore(File ignoreChild);
	
	/**
	 * Adds local svn:ignore property value to the list of ignores for a folder.
	 * 
	 * @param localIgnore 
	 */
	public void setIgnore(SvnIgnorePattern localIgnore);
	
	/**
	 * Creates a list of ignore patterns for the {@link #getPath() path}.
	 * 
	 * @return The ignore patterns for the folder
	 */
	public SvnIgnorePattern[] getIgnores();	
	
}
