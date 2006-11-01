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
public class ResourceHasLocalModificationsException extends
		WorkingCopyAccessException {

	public ResourceHasLocalModificationsException(File resource) {
		super(resource.getAbsolutePath());
		// TODO auto-generated
	}

}
