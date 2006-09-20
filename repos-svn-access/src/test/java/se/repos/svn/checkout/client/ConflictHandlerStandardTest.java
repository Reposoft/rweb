/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.io.IOException;

import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.TestFolder;

import junit.framework.TestCase;

public class ConflictHandlerStandardTest extends TestCase {

	public void testHandleConflictingFile() throws IOException {
		File dir = TestFolder.getNew();
		File f = new File(dir, "file.txt");
		f.createNewFile();
		File m = new File(dir, "file.txt.mine");
		m.createNewFile();
		File o = new File(dir, "file.txt.r15");
		o.createNewFile();
		File n = new File(dir, "file.txt.r17");
		n.createNewFile();
		
		ConflictHandler handler = new ConflictHandlerStandard();
		ConflictInformation conflict = handler.handleConflictingFile(f);
		
		assertEquals(f, conflict.getTargetPath());
		assertEquals(f, conflict.getMergedFile());
		assertEquals(m, conflict.getUserFile());
		assertEquals(o, conflict.getUsedRepositoryFile());
		assertEquals(n, conflict.getRepositoryFile());
		
		// test clean up
		handler.afterConflictResolved(conflict);
		assertTrue(f.exists());
		assertFalse(m.exists());
		assertFalse(o.exists());
		assertFalse(n.exists());
	}

}
