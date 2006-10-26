/* $license_header$
 */
package se.repos.svn.javahl;

import java.io.File;

import org.tigris.subversion.javahl.ClientException;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientAdapterFactory;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.javahl.AbstractJhlClientAdapter;
import org.tigris.subversion.svnclientadapter.javahl.JhlClientAdapterFactory;

import se.repos.svn.ClientProvider;
import se.repos.svn.UserCredentials;

public class JavahlClientProvider implements ClientProvider {

	private String runtimeConfigurationArea;

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
        
        setRuntimeConfigurationArea((AbstractJhlClientAdapter) svnClient);
        
        return svnClient;
	}

	// TODO seems like Javahl configDirectory concept sucks. the default client config directory is never set.
	private void setRuntimeConfigurationArea(AbstractJhlClientAdapter adapter) {
		try {
			this.runtimeConfigurationArea = adapter.getSVNClient().getConfigDirectory();
		} catch (ClientException e) { // we have already checked that svnClient is available
			throw new RuntimeException("Client library error. Could not use Javahl to get config directory.", e);
		}
	}

	public ISVNClientAdapter getSvnClient(UserCredentials login) {
		ISVNClientAdapter svnClient = getSvnClient();
		svnClient.setUsername(login.getUsername());
		svnClient.setPassword(login.getPassword());
		return svnClient;
	}

	public File getRuntimeConfigurationArea() {
		return new File(runtimeConfigurationArea);
	}

}
