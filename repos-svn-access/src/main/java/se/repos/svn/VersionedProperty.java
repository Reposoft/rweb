/* $license_header$
 */
package se.repos.svn;

/**
 * Models one subversion property for a file or folder.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface VersionedProperty {
	
	/**
	 * @return the property name, such as svn:keyword
	 */
	public String getName();
	
	/**
	 * @return the value, which is sometimes a whitespace separated list, null if the property does not exist
	 */
	public String getValue();
	
}
