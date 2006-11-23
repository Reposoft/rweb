/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;

import se.repos.svn.VersionedProperty;
import se.repos.svn.checkout.VersionedFileProperties;

public class PropertyAccessFile extends PropertyAccess 
	implements VersionedFileProperties {

	public PropertyAccessFile(File path, ISVNClientAdapter client) {
		super(path, client);
		if (!path.isFile()) throw new IllegalArgumentException("The path is not a file: " + path);
	}
	
	public boolean hasMimeType() {
		return super.hasProperty(MimeType.PROPERTY_NAME);
	}
	
	public VersionedProperty getMimeType() {
		return super.getProperty(MimeType.PROPERTY_NAME);
	}
	
	public void setMimeType(MimeType property) {
		if (property == null) {
			super.deleteProperty(MimeType.PROPERTY_NAME);
		} else {
			super.setProperty(property);
		}
	}

}
