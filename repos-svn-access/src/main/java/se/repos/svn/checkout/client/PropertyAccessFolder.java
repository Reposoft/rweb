/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.checkout.VersionedFolderProperties;

public class PropertyAccessFolder extends PropertyAccess
	implements VersionedFolderProperties {

	public PropertyAccessFolder(File path, ISVNClientAdapter client) {
		super(path, client);
		if (!path.isDirectory()) throw new IllegalArgumentException("The path is not a folder: " + path);
	}

	public SvnIgnorePattern[] getIgnores() {
		if (true) {
			throw new UnsupportedOperationException("Method PropertyAccessFolder#getIgnores not implemented yet");
		}
		return null;
	}

	public void setIgnore(SvnIgnorePattern localIgnore) {
		if (true) {
			throw new UnsupportedOperationException("Method PropertyAccessFolder#setIgnore not implemented yet");
		}
		
	}

}
