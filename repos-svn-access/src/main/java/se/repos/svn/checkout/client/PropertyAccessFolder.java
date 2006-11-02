/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.util.List;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientException;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.checkout.VersionedFolderProperties;
import se.repos.svn.checkout.WorkingCopyAccessException;

public class PropertyAccessFolder extends PropertyAccess
	implements VersionedFolderProperties {

	public PropertyAccessFolder(File path, ISVNClientAdapter client) {
		super(path, client);
		if (!path.isDirectory()) throw new IllegalArgumentException("The path is not a folder: " + path);
	}

	public SvnIgnorePattern[] getIgnores() {
		List ignored = null;
		try {
			ignored = client.getIgnoredPatterns(path);
		} catch (SVNClientException e) {
			WorkingCopyAccessException.handle(e);
		}
		return SvnIgnorePattern.array(ignored);
	}

	public void setIgnore(SvnIgnorePattern localIgnore) {
		try {
			client.addToIgnoredPatterns(path, localIgnore.getValue());
		} catch (SVNClientException e) {
			WorkingCopyAccessException.handle(e); // "Could not add ignore pattern " + localIgnore + " to path " + path, 
		}
	}

}
