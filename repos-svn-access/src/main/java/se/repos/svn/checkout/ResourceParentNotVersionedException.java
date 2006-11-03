/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.tigris.subversion.svnclientadapter.SVNClientException;

/**
 * 
 * Error message with javahl:
 * <pre>
Path is not a working copy directory
svn: 'C:\Documents and Settings\solsson\Lokala inställningar\Temp\TestReposSvnAccess24336dir\testAddNotRecursive1162545488103' is not a working copy
Det går inte att hitta sökvägen.  
svn: Can't open file 'C:\Documents and Settings\solsson\Lokala inställningar\Temp\TestReposSvnAccess24336dir\testAddNotRecursive1162545488103\.svn\entries': Det går inte att hitta sökvägen.
 * </pre>
 * 
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ResourceParentNotVersionedException extends
		ResourceNotVersionedException {

	private static final long serialVersionUID = 1L;

	private static final Pattern MATCH = Pattern.compile("^.*svn:\\s+'([^']+)'.*not a working copy.*$", Pattern.DOTALL);
	
	public static void identify(SVNClientException e) throws ResourceParentNotVersionedException {
		Matcher matcher = MATCH.matcher(e.getMessage());
		if (matcher.matches()) {
			File f = new File(matcher.group(1));
			throw new ResourceParentNotVersionedException(f);
		}	
	}
	
	/**
	 * @param path of the parent, not the original resource
	 */
	public ResourceParentNotVersionedException(File resource) {
		super(resource);
	}
	
}
