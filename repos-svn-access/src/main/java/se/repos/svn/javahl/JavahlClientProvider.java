/* $license_header$
 */
package se.repos.svn.javahl;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientAdapterFactory;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.javahl.JhlClientAdapterFactory;

import se.repos.svn.ClientProvider;
import se.repos.svn.config.ClientConfiguration;
import se.repos.svn.config.ConfigurationStateException;
import se.repos.svn.config.RuntimeConfigurationArea;

public class JavahlClientProvider implements ClientProvider {

	private static JavahlClientProvider provider = null;

	public static JavahlClientProvider getProvider() throws ClientNotAvaliableException {
		// avoid org.tigris.subversion.svnclientadapter.SVNClientException: factory for type svnkit already registered
		if (provider != null) return provider ;
		provider = new JavahlClientProvider();
		return provider;
	}
	
	private JavahlClientProvider() throws ClientNotAvaliableException {
		try {
			JhlClientAdapterFactory.setup();
		} catch (SVNClientException e) {
			throw new ClientNotAvaliableException(e);
		}
	}
	
	/**
	 * Forwarded to getSvnClient with default configuration area,
	 * which also serves to make sure that configuration is created if missing.
	 */
	public ISVNClientAdapter getSvnClient() {
        // config directory must be set even if it is the default, otherwise credentials can't be saved
        return getSvnClient(getDefaultRuntimeConfigurationArea());
	}

	public ISVNClientAdapter getSvnClient(File configFolder) {
		File auth = new File("auth");
		boolean authRemove = !auth.exists();
		
        ISVNClientAdapter svnClient;
		svnClient = SVNClientAdapterFactory.createSVNClient(
				JhlClientAdapterFactory.JAVAHL_CLIENT);
		
		try {
			svnClient.setConfigDirectory(configFolder);
		} catch (SVNClientException e) {
			throw new RuntimeException("Subversion client did not accept configuration area " + configFolder, e);
		}
		
		// the javahl client factory, new SVNClient(), creates an 'auth' folder in base dir for some reason
		if (authRemove && auth.exists()) {
			new File(auth, "svn.simple").delete();
			new File(auth, "svn.ssl.server").delete();
			new File(auth, "svn.username").delete();
			auth.delete();
		}
        
        if (svnClient == null) {
        	throw new RuntimeException("The Javahl client reports that it is avaliable, but it could not be created.");
        }
        
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
