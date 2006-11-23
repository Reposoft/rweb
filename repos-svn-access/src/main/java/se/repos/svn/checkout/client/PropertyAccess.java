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
	
	public boolean hasProperty(String name) {
		return get(name) != null;
	}

	public VersionedProperty getProperty(String name) {
		return get(name);
	}

	public void setProperty(VersionedProperty prop) {
		if (prop==null) throw new IllegalArgumentException("Can not set a null property. Use deleteProperty to remove.");
		if (prop.getName()==null) throw new IllegalArgumentException("Can not set property with name 'null'");
		if (prop.getName().length()==0) throw new IllegalArgumentException("Can not set property with empty name");
		if (prop.getValue()==null) throw new IllegalArgumentException("Invalid property: value is null");
		try {
			client.propertySet(path, prop.getName(), prop.getValue(), false);
		} catch (SVNClientException e) {
			WorkingCopyAccessException.handle(e); // offline operation because path is local
		}
	}
	
	/**
	 * Non recursive property remove
	 */
	public void deleteProperty(String name) {
		try {
			client.propertyDel(path, name, false);
		} catch (SVNClientException e) {
			WorkingCopyAccessException.handle(e); // offline operation because path is local
		}
	}
	
	/**
	 * Translate between svnClientAdapter properties and our propeties
	 * @param name property name
	 * @return the value, or null if no value
	 */
	private VersionedProperty get(String name) {
		ISVNProperty prop;
		try {
			prop = client.propertyGet(path, name);
		} catch (SVNClientException e) {
			WorkingCopyAccessException.handle(e); // offline operation because path is local
			return null;
		}
		if (prop == null) return null;
		return new PropertyWrapper(prop);
	}
	
	private class PropertyWrapper implements VersionedProperty {
		private ISVNProperty property;
		
		private PropertyWrapper(ISVNProperty property) {
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
