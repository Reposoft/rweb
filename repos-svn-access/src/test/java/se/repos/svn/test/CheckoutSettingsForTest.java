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
package se.repos.svn.test;

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
		"http://localhost/testrepo",
		"http://test.repos.se/testrepo"
	};
	
	// some tests require HTTPS repository
	public static final String[] REPOSITORIES_HTTPS = new String[] {
		"https://localhost/testrepo"
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
	
	/**
	 * Gets first existing URL from a list of candidate repositories.
	 */
	private static String getRepository(String[] list) {
		for (int i = 0; i < list.length; i++) {
			try {
				URL u = new URL(list[i]);
				try {
					u.openConnection().connect();
					System.out.println("Repository "+list[i]+" is available. Will be used for integration tests.");
				} catch (javax.net.ssl.SSLHandshakeException e) {
					System.out.println("Repository "+list[i]+" is available. Certificate must be accepted by the SVN client.");
				}
				return u.toString();
			} catch (MalformedURLException e) {
				throw new RuntimeException("Repository "+list[i]+" is invalid", e);
			} catch (IOException e) {
				System.out.println("Repository "+list[i]+" is not available, trying next one. Got: " + e.getMessage());
			}
		}
		throw new RuntimeException("None of the repositories in REPOSITORIES are available. Can not do integraiton tests.");
	}
	
	/**
	 * @param projectName identifier for project, will be used as folder name
	 */
	public CheckoutSettingsForTest() {
		this(false);
	}
	
	/**
	 * @param projectName identifier for project, will be used as folder name
	 * @param needSSL true if an SSL repository is required
	 *  (with a certificate that matches the hostname, but is not signed by trusted CA)
	 */
	public CheckoutSettingsForTest(boolean needSSL) {
		super(getRepository(needSSL ? REPOSITORIES_HTTPS : REPOSITORIES)
				+ "/" + PROJECT_NAME + "/trunk/" + TEST_FOLDER, 
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
