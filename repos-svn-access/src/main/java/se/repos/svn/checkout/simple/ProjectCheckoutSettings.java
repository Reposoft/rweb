/* $license_header$
 */
package se.repos.svn.checkout.simple;

import java.io.File;
import java.net.MalformedURLException;

import se.repos.svn.UserCredentials;
import se.repos.svn.checkout.ImmutableUserCredentials;
import se.repos.svn.checkout.client.AbstractCheckoutSettings;
import se.repos.svn.project.RejectInvalidProjectName;
import se.repos.validation.Validation;
import se.repos.validation.ValidationRule;

/**
 * The repository is assumed to use the standard folders 
 * {@link http://svnbook.red-bean.com/nightly/en/svn.intro.quickstart.html}
 * so if repository is given as <code>http://server/repository</code> and the project is <code>myproject</code>
 * the latest contents will be checked out from <code>http://server/repository/myproject/trunk</code>.
 */
public class ProjectCheckoutSettings extends AbstractCheckoutSettings {

	/**
	 * Validation rule for project name, so it will work as a directory name
	 */
	public static final ValidationRule<String> PROJECT_NAME_RULE = Validation.rule(RejectInvalidProjectName.class);
	
	private UserCredentials userCredentials;
	
	public ProjectCheckoutSettings(
			String repositoryRootUrl, String projectName, 
			File workingCopyFolder,
			String username, String password) throws MalformedURLException {
		super(getRepositoryUrl(repositoryRootUrl, projectName), workingCopyFolder);
		this.userCredentials = new ImmutableUserCredentials(username, password);
	}
	
	public UserCredentials getLogin() {
		return userCredentials;
	}

	private static String getRepositoryUrl(String repositoryRootUrl, String projectName) {
		PROJECT_NAME_RULE.validate(projectName);
		return repositoryRootUrl + "/" + projectName + "/trunk";
	}
}
