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
	 * Retreives a property value for the current path and revision of the working copy.
	 * @param name The property name
	 * @return The current value of the named property, same as in the checked out revision if the path has no local changes.
	 */
	public VersionedProperty getProperty(String name);
	
	/**
	 * Sets a property value in the working copy that will be committed in the next revision.
	 * @param nameAndValue The name and value to set, ovewriting any existing property of the same name.
	 */
	public void setProperty(VersionedProperty nameAndValue);
	
}
