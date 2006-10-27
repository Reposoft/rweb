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
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;

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

	// the repository to integration test in. tried in array order.
	public static final String[] REPOSITORIES = new String[] {
		"https://localhost/testrepo",
		"http://test.repos.se/testrepo"
	};
	
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
	
	private static String getRepository() {
		for (int i = 0; i < REPOSITORIES.length; i++) {
			try {
				URL u = new URL(REPOSITORIES[i]);
				try {
					u.openConnection().connect();
					System.out.println("Repository "+REPOSITORIES[i]+" is available. Will be used for integration tests.");
				} catch (javax.net.ssl.SSLHandshakeException e) {
					System.out.println("Repository "+REPOSITORIES[i]+" is available. Certificate must be accepted by the SVN client.");
				}
				return u.toString();
			} catch (MalformedURLException e) {
				throw new RuntimeException("Repository "+REPOSITORIES[i]+" is invalid", e);
			} catch (IOException e) {
				e.printStackTrace();
				System.out.println("Repository "+REPOSITORIES[i]+" is not available, trying next one. Got: " + e.getMessage());
			}
		}
		throw new RuntimeException("None of the repositories in REPOSITORIES are available. Can not do integraiton tests.");
	}
	
	/**
	 * @param projectName identifier for project, will be used as folder name
	 * @param workingCopyDirectory the root for the working copy, corresponding to {@link #getCheckoutUrl()}
	 */
	public CheckoutSettingsForTest() {
		super(getRepository() + "/" + PROJECT_NAME + "/trunk/" + TEST_FOLDER, 
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
