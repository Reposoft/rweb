/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.ISVNProperty;
import org.tigris.subversion.svnclientadapter.SVNClientException;

import se.repos.svn.VersionedProperty;
import se.repos.svn.checkout.VersionedProperties;
import se.repos.svn.checkout.WorkingCopyAccessException;

public class PropertyAccess implements VersionedProperties {

	protected File path;
	protected ISVNClientAdapter client;

	/**
	 * 
	 * @param path
	 * @param client
	 * @throws IllegalArgumentException if the path is invalid for property access
	 */
	public PropertyAccess(File path, ISVNClientAdapter client) {
		this.path = path;
		this.client = client;
		if (!path.exists()) throw new IllegalArgumentException("Can not access properties for the non-existing path " + path);
	}
	
	public File getPath() {
		return path;
	}

	public VersionedProperty getProperty(String name) {
		try {
			return new PropertyWrapper(client.propertyGet(path, name));
		} catch (SVNClientException e) {
			throw new WorkingCopyAccessException(e); // offline operation because path is local
		}
	}

	public void setProperty(VersionedProperty nameAndValue) {
		if (true) {
			throw new UnsupportedOperationException("Method VersionedPropertiesAccess#setProperty not implemented yet");
		}
		
	}
	
	private class PropertyWrapper implements VersionedProperty {
		private ISVNProperty property;

		PropertyWrapper(ISVNProperty property) {
			this.property = property;
		}

		public String getName() {
			return property.getName();
		}

		public String getValue() {
			return property.getValue();
		}
	}

}
