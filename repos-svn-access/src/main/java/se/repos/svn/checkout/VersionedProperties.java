/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

import se.repos.svn.VersionedProperty;

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
	 * Checks if the resource has a specific property
	 * @param name The property name
	 * @return true if the property exists (even if value is empty) and false if not
	 */
	public boolean hasProperty(String name);
	
	/**
	 * Retreives a property value for the current path and revision of the working copy.
	 * @param name The property name
	 * @return The current value of the named property, or null if the property is not set.
	 *  To get the latest property value from the repository, do update on the resource first.
	 */
	public VersionedProperty getProperty(String name);
	
	/**
	 * Sets a property value in the working copy that will be committed in the next revision.
	 * @param nameAndValue The name and value to set, ovewriting any existing property of the same name.
	 *  Use a property with {@link VersionedProperty#getValue() value}<code>=null</code> to delete a property.
	 */
	public void setProperty(VersionedProperty nameAndValue);
	
	/**
	 * Unsets a property, so that hasProperty evaluates to false and getProperty returns null
	 * @param name the property to remove
	 */
	public void deleteProperty(String name);
	
}
