/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;
import java.io.IOException;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.simple.PersonalWorkingCopyIntegrationTest;
import junit.framework.TestCase;

/**
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo when there is more than one working copy implementation, make this abstract and use to test all implementations
 */
public class ReposWorkingCopyIntegrationTest extends TestCase {

	// test workingcopy path
	private File path;
	
	private ReposWorkingCopy client;
	
	protected void setUp() throws Exception {
		super.setUp();
		CheckoutSettings settings = new CheckoutSettingsForTest();
		path = settings.getWorkingCopyDirectory();
		client = init(settings);
	}
	
	protected ReposWorkingCopy init(CheckoutSettings settings) {
		return ReposWorkingCopyFactory.getClient(settings);
	}

	public void testAdd() {
		File created = new File(path, "newfile.txt");
		client.add(created);
		//CheckStatus.expect(path, CheckStatus.ADDED);
	}
	
	public void testAddOutsideWorkingCopy() throws IOException {
		File tmp = File.createTempFile("PersonalWorkingCopyTestFile", "file");
		tmp.deleteOnExit();
		client.add(tmp);
		fail("Should have reported error because the new folder is not inside a working copy");
	}

	public void testDelete() {
		File created = new File(path, "tobedeleted.txt");
		client.add(created);
		//CheckStatus.expect(path, CheckStatus.ADDED);
		client.delete(created);
		//CheckStatus.expect(path, CheckStatus.ADDED);
	}
	
	public void testDeleteAlreadyDeletedFile() {
		File created = new File(path, "tobedeleted.txt");
		client.add(created);
		//CheckStatus.expect(path, CheckStatus.ADDED);
		created.delete();
		client.delete(created);
		//CheckStatus.expect(path, CheckStatus.ADDED);
		fail("Should not accept to delete a file that is gone, or should it?");
	}

	public void testMove() {
		fail("Not yet implemented");
	}

	public void testMoveAlreadyMovedFolder() {
		fail("Not yet implemented");
	}	
	
}
