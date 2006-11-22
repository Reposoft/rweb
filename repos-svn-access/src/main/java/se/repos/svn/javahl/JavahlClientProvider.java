/* $license_header$
 */
package se.repos.svn.javahl;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientAdapterFactory;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.javahl.JhlClientAdapterFactory;

import se.repos.svn.ClientProvider;
import se.repos.svn.UserCredentials;
import se.repos.svn.config.ClientConfiguration;
import se.repos.svn.config.ConfigurationStateException;
import se.repos.svn.config.RuntimeConfigurationArea;

public class JavahlClientProvider implements ClientProvider {

	public JavahlClientProvider() throws ClientNotAvaliableException {
		try {
			JhlClientAdapterFactory.setup();
		} catch (SVNClientException e) {
			throw new ClientNotAvaliableException(e);
		}
	}
	
	public ISVNClientAdapter getSvnClient() {
        ISVNClientAdapter svnClient;
		svnClient = SVNClientAdapterFactory.createSVNClient(
				JhlClientAdapterFactory.JAVAHL_CLIENT);
        
        if (svnClient == null) {
        	throw new RuntimeException("The Javahl client reports that it is avaliable, but it could not be created.");
        }
        
        return svnClient;
	}

	public ISVNClientAdapter getSvnClient(UserCredentials login) {
		ISVNClientAdapter svnClient = getSvnClient();
		svnClient.setUsername(login.getUsername());
		svnClient.setPassword(login.getPassword());
		return svnClient;
	}

	/**
	 * Creates default repos-svn-access configuration instance
	 */
	public ClientConfiguration getRuntimeConfiguration(File clientConfigurationFolder) throws ConfigurationStateException {
		return new RuntimeConfigurationArea(clientConfigurationFolder);
	}

	public File getDefaultRuntimeConfigurationArea() {
		return RuntimeConfigurationArea.getDefaultConfigFolder();
	}

}
