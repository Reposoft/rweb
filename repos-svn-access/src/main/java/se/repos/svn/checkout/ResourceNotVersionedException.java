/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.tigris.subversion.svnclientadapter.SVNClientException;

/**
 * Represents the quite common error that an operation was attempted on a non-versioned file or folder.
 * 
 * Only add and status can be done on non-versioned resources.
 * 
 * The error message is something like this in javahl:
 * <pre>
Tried a versioning operation on an unversioned resource
svn: 'C:\Documents and Settings\solsson\Lokala inställningar\Temp\folder\nonversioned.txt' is not under version control
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

	private static final Pattern MATCH = Pattern.compile("^.*svn:\\s+'([^']+)'.*not under version control.*$", Pattern.DOTALL);
	
	static void identify(SVNClientException e) throws ResourceNotVersionedException {
		Matcher matcher = MATCH.matcher(e.getMessage());
		if (matcher.matches()) {
			File f = new File(matcher.group(1));
			throw new ResourceNotVersionedException(f);
		}
	}
	
	public ResourceNotVersionedException(File resource) {
		super(resource.getAbsolutePath());
		this.path = resource;
	}

	public File getPath() {
		return this.path;
	}

}
