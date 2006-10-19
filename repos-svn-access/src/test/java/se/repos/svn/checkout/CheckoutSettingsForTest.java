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
package se.repos.svn.checkout;

import java.io.File;

import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.ImmutableUserCredentials;
import se.repos.svn.checkout.client.AbstractCheckoutSettings;

/**
 * Test settings, assuming standard project reporsitory layout.
 *
 * The URL is given as repository root, but returned with the 'trunk' folder.
 *
 * @author Staffan Olsson
 * @since 2006-apr-15
 * @version $Id: CheckoutSettingsForTest.java 1634 2006-09-18 07:11:28Z solsson $
 */
public class CheckoutSettingsForTest extends AbstractCheckoutSettings {

	/**
	 * Root url for the test repository
	 */
	public static final String ROOT_URL = "https://localhost/testrepo";
	
	/**
	 * The project, same as the folder name under repository root, no slashes
	 */
	public static final String PROJECT_NAME = "test"; 
	
	/**
	 * Use a subfolder of the test account, so that everything is not checked out.
	 */
	private static final String TEST_FOLDER = "repos-svn-access";
	
	/**
	 * Temporary projectengine account.
	 * All users need their own account to get a nice history / commit log.
	 */
	public static final String USERNAME = "test";
	
	/**
	 * Temporary password for this account
	 */
	public static final String PASSWORD = "test";
	
	private UserCredentials userCredentials = new ImmutableUserCredentials(USERNAME, PASSWORD);
	
	/**
	 * @param projectName identifier for project, will be used as folder name
	 * @param workingCopyDirectory the root for the working copy, corresponding to {@link #getCheckoutUrl()}
	 */
	public CheckoutSettingsForTest() {
		super(ROOT_URL + "/" + PROJECT_NAME + "/trunk/" + TEST_FOLDER, 
				getEmptyTemporaryDirectory());
	}

	public UserCredentials getLogin() {
		return userCredentials;
	}

	static File getEmptyTemporaryDirectory() {
		File tmp = TestFolder.getNew();
		System.out.println("Using temporary directory: " + tmp.getAbsolutePath());
		return tmp;
	}

	public static void tearDown() {
		TestFolder.cleanUp();
	}
}
