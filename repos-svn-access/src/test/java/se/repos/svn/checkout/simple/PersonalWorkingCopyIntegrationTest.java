/* Copyright 2006 Optime data Sweden
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package se.repos.svn.checkout.simple;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ReposWorkingCopy;

import junit.framework.TestCase;

public class PersonalWorkingCopyIntegrationTest extends TestCase {
	
	private static final String TEST_FILE = "automated-test-increment.txt";
	
	// folder for test working copy. expected to have deleteOnExit.
	private File tmpFolder = getEmptyTemporaryDirectory();
	
	public void testWorkflow() throws IOException {
		// first instantiate in new folder
		// Note that currently the username and password for ProjectEngine
		//  is hard coded into CheckoutSettingsForProject 
		CheckoutSettings settings = 
			new CheckoutSettingsForTest(tmpFolder);
		ReposWorkingCopy workingCopy = new PersonalWorkingCopy(settings);
		
		// this should cause a checkout right here
		assertTrue("Should now find a .svn folder inside checkout dir",
				new File(tmpFolder.getAbsolutePath() + '/' + ".svn").exists());
		
		// should be able to do an update
		try {
			workingCopy.update();
		} catch (ConflictException e1) {
			// Tested in PersonalWorkingCopyConflictTest
			fail("Unexpected conflict. Maybe the test is running somewhere else too. Try again.");
		}
		assertFalse("there should be no local changes", workingCopy.hasLocalChanges());
		
		// synchronize should do the same thing now because there are no locks or local changes
		try {
			workingCopy.synchronize();
		} catch (ConflictException e) {
			fail("Unexpected conflict. Maybe the test is running somewhere else too. Try again.");
		}
		
		// get the automated test file
		File testFile = new File(settings.getWorkingCopyDirectory().getAbsolutePath() + "/" + TEST_FILE);
		increaseCounter(testFile);
		
		// local changes
		assertTrue("Now there should be local changes (counting also files that have not been added yet",
				workingCopy.hasLocalChanges());
		
		// an update should not affect the changes
		try {
			workingCopy.update();
		} catch (ConflictException e1) {
			fail("Unexpected conflict. Maybe the test is running somewhere else too. Try again.");
		}
		assertTrue("Update does not replace the changed file", workingCopy.hasLocalChanges());
				
		// it should be possible to close the program and next time open the same working copy again
		workingCopy = null;
		workingCopy = new PersonalWorkingCopy(settings);
		
		assertTrue("The changes should still be here if the working copy is reopened",
				workingCopy.hasLocalChanges());
		
		
		// syncronize, should add and commit changes
		try {
			workingCopy.synchronize();
		} catch (ConflictException e) {
			fail("Unexpected conflict. Maybe the test is running somewhere else too. Try again.");
		}
		
		assertFalse("Now the local changes should not be local anymore, they should be commited",
				workingCopy.hasLocalChanges());
		
	}

	private void increaseCounter(File testFile) throws IOException, FileNotFoundException {
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
	}
	
	private File getEmptyTemporaryDirectory() {
		try {
			File tmp = File.createTempFile("PersonalWorkingCopyTest", "dir");
			tmp.delete();
			tmp.mkdir();
			tmp.deleteOnExit();
			System.out.println("Using temporary directory: " + tmp.getAbsolutePath());
			return tmp;
		} catch (IOException e) {
			// TODO auto-generated
			throw new RuntimeException("IOException handling missing", e);
		}
		
	}

/*	public void testHasLocalChanges() {

	}

	public void testSynchronize() {

	}

	public void testLock() {

	}

	public void testUpdate() {

	}*/

}
