/* $license_header$
 */
package se.repos.svn.checkout.commontests;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;

import junit.framework.TestCase;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.ReposWorkingCopyFactory;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.test.CheckoutSettingsForTest;

/**
 * If this tests does not pass, other integration test will also fail.
 * 
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo when there is more than one working copy implementation, make this abstract and use to test all implementations
 */
public class CheckoutUpdateCommitIntegrationTest extends TestCase {

	/**
	 * A text file with an integer that should be found in the {@link CheckoutSettingsForTest} working copy.
	 */
	public static final String TEST_FILE = "automated-test-increment.txt";
	
	public void testWorkflow() throws IOException, RepositoryAccessException, ConflictException {
	
		CheckoutSettings settings = new CheckoutSettingsForTest();
		File path = settings.getWorkingCopyFolder();
		File testFile = new File(path, TEST_FILE);
		
		ReposWorkingCopy client = AllTests.getClient(settings, getName());
		
		client.checkout();
		
		// this should cause a checkout right here
		assertTrue("Should have checked out the test file ", testFile.exists());
		
		// should be able to do an update
		client.update();
		assertFalse("There should be no local changes to the working copy", client.hasLocalChanges());
		
		// get the automated test file
		int count = increaseCounter(testFile);
		
		// we have changed a versioned file's contents
		assertTrue("Now there should be local changes", client.hasLocalChanges(testFile));
		
		// an update should not affect the changes
		client.update();
		assertTrue("Update does not replace the changed file", client.hasLocalChanges(testFile));
				
		// it should be possible to close the program and next time open the same working copy again
		ReposWorkingCopy client2 = ReposWorkingCopyFactory.getClient(settings);
		assertTrue("A second client should see the same changes", client2.hasLocalChanges(testFile));
		
		// commit changes, but always do update first to check for conflicts
		client.update();
		client.commit("Increased test counter for basic workflow test to " + count);
		
		assertFalse("Everything committed, so working copy should be sync with the repository " +
				"(except that it might have contents that are not versioned yet)", client.hasLocalChanges());
		
		CheckoutSettingsForTest.tearDown();
	}
	
	public void testUpdateSingleFile() {
		// TODO
	}

	// helper method for testcase
	public static int increaseCounter(File testFile) throws IOException, FileNotFoundException {
		if (!testFile.exists()) {
			testFile.createNewFile();
			FileWriter fout = new FileWriter(testFile);
			fout.write("0");
			fout.close();
		}
		// read current number
		FileReader fin = new FileReader(testFile);
		BufferedReader in = new BufferedReader(fin);
		int count = Integer.parseInt(in.readLine());
		in.close();
		fin.close();
		// write incremented number
		FileWriter fout = new FileWriter(testFile);
		fout.write("" + ++count);
		fout.close();
		return count;
	}
	
}
