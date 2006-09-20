/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;
import java.io.IOException;

public abstract class TestFolder {

	public static File getNew() {
		try {
			File tmp = File.createTempFile("ReposWorkingCopyTest", "dir");
			tmp.delete();
			tmp.mkdir();
			tmp.deleteOnExit();
			return tmp;
		} catch (IOException e) {
			throw new RuntimeException("Could not create temp folder. Test can not continue.", e);
		}
	}
	
}
