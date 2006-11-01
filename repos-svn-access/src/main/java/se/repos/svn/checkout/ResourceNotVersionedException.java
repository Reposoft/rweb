/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

/**
 * Represents the quite common error that an operation was attempted on a non-versioned file or folder.
 * 
 * Only add and status can be done on non-versioned resources.
 * 
 * The error message is something like this in javahl:
 * <pre>
Tried a versioning operation on an unversioned resource
svn: 'C:\Documents and Settings\solsson\Lokala inst�llningar\Temp\folder\nonversioned.txt' is not under version control
 * </pre>
 * And like this in JavaSVN:
 * <pre>svn: 'C:\DOCUME~1\solsson\LOKALA~1\Temp\folder\nonversioned.txt' is not under version control</pre>
 * 
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ResourceNotVersionedException extends
		WorkingCopyAccessException {

	private static final long serialVersionUID = 1L;
	private File path;

	public ResourceNotVersionedException(File resource) {
		super(resource.getAbsolutePath());
		this.path = resource;
	}

	public File getPath() {
		return this.path;
	}

}
