/* $license_header$
 */
package se.repos.svn;

/**
 * Models one subversion property for a file or folder.
 * 
 * If a property is requested but does not exist, <code>null</code>
 * may be returned instead of an instance. There should be methods
 * to check if a property exists, so that null value handling is not required.
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
	 * @return the value, which is sometimes a whitespace separated list
	 */
	public String getValue();
	
}
