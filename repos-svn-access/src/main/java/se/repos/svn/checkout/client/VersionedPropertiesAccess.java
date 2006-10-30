/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;

import se.repos.svn.VersionedProperty;
import se.repos.svn.checkout.VersionedProperties;

public class VersionedPropertiesAccess implements VersionedProperties {

	private File path;
	private ISVNClientAdapter client;

	public VersionedPropertiesAccess(File path, ISVNClientAdapter client) {
		this.path = path;
		this.client = client;
	}
	
	public File getPath() {
		return path;
	}

	public VersionedProperty getProperty(String name) {
		if (true) {
			throw new UnsupportedOperationException("Method VersionedPropertiesAccess#getProperty not implemented yet");
		}
		return null;
	}

	public void setProperty(VersionedProperty nameAndValue) {
		if (true) {
			throw new UnsupportedOperationException("Method VersionedPropertiesAccess#setProperty not implemented yet");
		}
		
	}

}
