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

import java.io.File;
import java.net.MalformedURLException;

import org.tigris.subversion.svnclientadapter.SVNUrl;

import se.repos.svn.RepositoryUrl;
import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.project.RejectInvalidProjectName;
import se.repos.validation.Validation;
import se.repos.validation.ValidationRule;

/**
 * Default implementation, assuming standard project reporsitory layout.
 *
 * The URL is given as repository root, and returned with the 'trunk' folder.
 *
 * @author Staffan Olsson
 * @since 2006-apr-15
 * @version $Id$
 */
public class CheckoutSettingsForProject implements CheckoutSettings {

	/**
	 * Root url for the test repository, ends with '/'
	 */
	public static final String ROOT_URL = "http://alto.optime.se/testrepo/";
	
	/**
	 * The project, same as the folder name under repository root, no slashes
	 */
	public static final String PROJECT_NAME = "test"; 
	
	/**
	 * Temporary projectengine account.
	 * All users need their own account to get a nice history / commit log.
	 */
	public static final String USERNAME = "test";
	
	/**
	 * Temporary password for this account
	 */
	public static final String PASSWORD = "test"; 
	
	/**
	 * Validation rule for project name, so it will work as a directory name
	 */
	public static final ValidationRule<String> PROJECT_NAME_RULE = Validation.rule(RejectInvalidProjectName.class);
	
	private SharedUserCredentials credentials = new SharedUserCredentials(USERNAME, PASSWORD);
	private File workingCopyDirectory;
	private RepositoryUrl repositoryUrl;
	
	/**
	 * @param projectName identifier for project, will be used as folder name
	 * @param workingCopyDirectory the root for the working copy, corresponding to {@link #getCheckoutUrl()}
	 */
	public CheckoutSettingsForProject(File workingCopyDirectory) {
		PROJECT_NAME_RULE.validate(PROJECT_NAME);
		this.repositoryUrl = new ProjectRepositoryUrl(PROJECT_NAME);
		this.workingCopyDirectory = workingCopyDirectory;
	}
	
	public File getWorkingCopyDirectory() {
		return workingCopyDirectory;
	}

	public RepositoryUrl getCheckoutUrl() {
		return repositoryUrl;
	}

	public UserCredentials getLogin() {
		return credentials;
	}
	
	/**
	 * The most basic user credentials implementation imaginable.
	 * Immutable, to be passed freely around the application for authenticating the current user.
	 * @version $Id$
	 */
	private class SharedUserCredentials implements UserCredentials {
		String username;
		String password;
		
		SharedUserCredentials(String username, String password) {
			this.username = username;
			this.password = password;
		}
		public String getUsername() {
			return username;
		}
		public String getPassword() {
			return password;
		}
		public String toString() {
			return getUsername() + ':' + password.replaceAll(".", "*");
		}
	}
	
	/**
	 * The repository is assumed to use the standard folders 
	 * {@link http://svnbook.red-bean.com/nightly/en/svn.intro.quickstart.html}
	 * so if repository is given as <code>http://server/repository/myproject</code>
	 * the latest contents will be checked out from <code>http://server/repository/myproject/trunk</code>.
	 */
	private class ProjectRepositoryUrl implements RepositoryUrl {
		private SVNUrl url;	
		ProjectRepositoryUrl(String projectName) {
			try {
				this.url = new SVNUrl(ROOT_URL + projectName + "/trunk/");
			} catch (MalformedURLException e) {
				throw new RuntimeException("MalformedURLException handling missing", e);
			}
		}
		public SVNUrl getUrl() {
			return url;
		}
		public String toString() {
			return getUrl().toString();
		}
		public boolean equals(Object url) {
			return this.url.toString().equals(url.toString());
		}
	}

	@Override
	public String toString() {
		return "svn checkout " + getLogin() + ' ' + getCheckoutUrl() + ' ' + getWorkingCopyDirectory();
	}
	
	

}
