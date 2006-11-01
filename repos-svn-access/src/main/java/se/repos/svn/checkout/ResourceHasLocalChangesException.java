/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

/**
 * The error message is something like this in javahl:
 * <pre>
Attempting restricted operation for modified resource
svn: 'C:\Documents and Settings\solsson\Lokala inställningar\Temp\folder\to be deleted.txt' has local modifications
 * </pre>
 * And like this in JavaSVN:
 * <pre>svn: 'to be deleted.txt' has local modifications</pre>
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ResourceHasLocalChangesException extends
		WorkingCopyAccessException {

	private static final long serialVersionUID = 1L;
	private File path;
	
	public ResourceHasLocalChangesException(File resource) {
		super(resource.getAbsolutePath());
		this.path = resource;
	}

	public File getPath() {
		return path;
	}

}
