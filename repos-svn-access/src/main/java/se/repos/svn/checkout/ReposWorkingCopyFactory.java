/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientException;

import se.repos.svn.ClientProvider;
import se.repos.svn.ClientProvider.ClientNotAvaliableException;
import se.repos.svn.checkout.client.ConflictHandler;
import se.repos.svn.checkout.client.ConflictHandlerStandard;
import se.repos.svn.checkout.client.ReposWorkingCopySvn;
import se.repos.svn.config.ClientConfiguration;
import se.repos.svn.config.ConfigurationStateException;
import se.repos.svn.javahl.JavahlClientProvider;
import se.repos.svn.svnkit.SvnKitClientProvider;

/**
 * A factory to instantiate a default ReposWorkingCopy implementatoin,
 * for applications that don't use dependency injection.
 * 
 * Should verify that the required client libraries are available.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo implement check that the client provider library exists, see ClientProvider interface
 */
public abstract class ReposWorkingCopyFactory {

	private static Logger logger = LoggerFactory.getLogger(ReposWorkingCopyFactory.class);
	
	private static ClientProvider clientProvider = null;
	
	//private static File notDefaultConfigurationArea = null;

	private static ReposWorkingCopySvn getNewWorkingCopy(
			ISVNClientAdapter clientAdapter, 
			ClientConfiguration clientConfiguration, 
			CheckoutSettings settings) {
		logger.info("Creating new working copy client for path {}", settings.getWorkingCopyFolder());
		return new ReposWorkingCopySvn(
				clientAdapter,
				clientConfiguration,
				settings,
				new ConflictHandlerStandard());
	}

	private static ISVNClientAdapter getClientAdapter(ClientProvider clientProvider, CheckoutSettings settings) {
		return clientProvider.getSvnClient(settings.getLogin());
	}
	
    /**
     * Creates a working copy instance and sets a {@link ConflictHandler} to it.
     * @param settings User session (url and authentication)
     * @return New client, based on the client adapter returned from ClientProvider.
     * @throws ConfigurationStateException 
     */
	public static ReposWorkingCopy getClient(CheckoutSettings settings) {
		ClientProvider clientProvider = getClientProvider();
		ClientConfiguration clientConfiguration;
		try {
			clientConfiguration = clientProvider.getRuntimeConfiguration(
					clientProvider.getDefaultRuntimeConfigurationArea());
		} catch (ConfigurationStateException e) {
			throw new RuntimeException("The shared configuration for Subversion clients in this user profile is invalid", e);
		}
		ISVNClientAdapter clientAdapter = getClientAdapter(clientProvider, settings);
		ReposWorkingCopySvn wc = getNewWorkingCopy(clientAdapter, clientConfiguration, settings);
		return wc;
	}
	
	/**
	 * 
	 * @param settings
	 * @param runtimeConfigurationArea
	 * @return
	 * @throws ConfigurationStateException
	 */
	public static ReposWorkingCopy getClient(CheckoutSettings settings, File runtimeConfigurationArea) throws ConfigurationStateException {
		File configurationArea = runtimeConfigurationArea;
		ClientProvider clientProvider = getClientProvider();
		ClientConfiguration clientConfiguration = clientProvider.getRuntimeConfiguration(configurationArea);
		ISVNClientAdapter clientAdapter = getClientAdapter(clientProvider, settings);
		try {
			// here's the only chance we have to synchronize the actual config-dir with the configuration model
			clientAdapter.setConfigDirectory(configurationArea);
			logger.info("Using custom configuration area {}", configurationArea);
		} catch (SVNClientException e) {
			throw new ConfigurationStateException("Subversion client did not accept configuration area " + runtimeConfigurationArea, e);
		}
		ReposWorkingCopySvn wc = getNewWorkingCopy(clientAdapter, clientConfiguration, settings);
		return wc;
	}
	
	/**
	 * Creates the javahl client if available.
	 * Last resort is the SvnKit client, that needs a license for redistribution.
	 * @throws RuntimeException if there is no client library available. This is considered a deployment issue.
	 */
	private static ClientProvider getClientProvider() throws RuntimeException {
		if (clientProvider!=null) return clientProvider; // javachl can not be initialize twice
		// we prefer javahl
		try {
			clientProvider = new JavahlClientProvider();
			logger.info("Using Javahl client library. See license: http://subversion.tigris.org/license-1.html");
			return clientProvider;
		} catch (ClientNotAvaliableException e) {
			// it can be very picky about the dll version, so it might be that the dll exists but is invalid
			logger.info("Javahl client library is not available. Is the library file 'libsvnjavahl-1' present?");
		}
	    // try the pure java library. it can be installed by simply adding the jar.
		try {
			clientProvider = new SvnKitClientProvider();
			logger.warn("Using the SvnKit library. For commercial use this requires a license. See http://svnkit.com/.");
			return clientProvider;
		} catch (ClientNotAvaliableException e) {
			logger.info("SvnKit client library is not available. Is 'svnkit.jar' present?");
		}
		// all alternatives failed
	    throw new RuntimeException("There is no SVN client avaliable. Tried Javahl and SvnKit.");
	}

}
