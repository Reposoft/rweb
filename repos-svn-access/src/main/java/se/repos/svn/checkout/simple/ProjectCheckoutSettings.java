/* $license_header$
 */
package se.repos.svn.checkout.simple;

import java.io.File;
import java.util.regex.Pattern;

import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.ImmutableUserCredentials;
import se.repos.svn.checkout.client.AbstractCheckoutSettings;

/**
 * The repository is assumed to use the standard folders 
 * {@link http://svnbook.red-bean.com/nightly/en/svn.intro.quickstart.html}
 * so if repository is given as <code>http://server/repository</code> and the project is <code>myproject</code>
 * the latest contents will be checked out from <code>http://server/repository/myproject/trunk</code>.
 */
public class ProjectCheckoutSettings extends AbstractCheckoutSettings {

	/**
	 * Validation rule for project name, so it will work as a directory name.
	 * This should be the common repos.se rule.
	 */
	public static final Pattern PROJECT_NAME_RULE = Pattern.compile("[a-zA-Z][\\w-]*");
	
	private UserCredentials userCredentials;
	
	public ProjectCheckoutSettings(
			String repositoryRootUrl, String projectName, 
			File workingCopyFolder,
			String username, String password) throws IllegalArgumentException {
		super(getRepositoryUrl(repositoryRootUrl, projectName), workingCopyFolder);
		this.userCredentials = new ImmutableUserCredentials(username, password);
	}
	
	public UserCredentials getLogin() {
		return userCredentials;
	}

	private static String getRepositoryUrl(String repositoryRootUrl, String projectName) {
		if (PROJECT_NAME_RULE.matcher(projectName).matches()) {
			return repositoryRootUrl + "/" + projectName + "/trunk";
		}
		throw new IllegalArgumentException("Project name is invalid: " + projectName);
	}
	
}
