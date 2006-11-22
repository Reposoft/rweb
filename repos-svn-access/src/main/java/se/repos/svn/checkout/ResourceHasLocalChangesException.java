/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.tigris.subversion.svnclientadapter.SVNClientException;

/**
 * The error message is something like this in javahl:
 * <pre>
Attempting restricted operation for modified resource
svn: 'C:\Documents and Settings\solsson\Lokala inställningar\Temp\folder\to be deleted.txt' has local modifications
 * </pre>
 * And like this in SvnKit:
 * <pre>svn: 'to be deleted.txt' has local modifications</pre>
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ResourceHasLocalChangesException extends
		WorkingCopyAccessException {

	private static final long serialVersionUID = 1L;
	private File path;
	
	private static final Pattern MATCH = Pattern.compile("^.*svn:\\s+'([^']+)'.*has local modifications.*$", Pattern.DOTALL);
	
	static void identify(SVNClientException e) throws ResourceHasLocalChangesException {
		Matcher matcher = MATCH.matcher(e.getMessage());
		if (matcher.matches()) {
			File f = new File(matcher.group(1));
			throw new ResourceHasLocalChangesException(f);
		}
	}
	
	public ResourceHasLocalChangesException(File resource) {
		super(resource.getAbsolutePath());
		this.path = resource;
	}

	public File getPath() {
		return path;
	}

}
