/* $license_header$
 */
package se.repos.svn.checkout.client;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;

import se.repos.svn.checkout.ReposWorkingCopy;

/**
 * Test helper to get the client adapter from a repos client.
 * The method may be visible only in same package.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public abstract class GetClientAdapter {

	/**
	 * Get the current client adapter, can be used to set mock backend.
	 * @param workingCopy used in tests
	 * @return the svnClientAdapter client
	 */
	public static ISVNClientAdapter from(ReposWorkingCopy workingCopy) {
		return ((ReposWorkingCopySvn) workingCopy).getClientAdapter();
	}
	
}
