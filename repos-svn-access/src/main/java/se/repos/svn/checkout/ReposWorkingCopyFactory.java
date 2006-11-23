/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;

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
	
    /**
     * Creates a working copy instance
     * @param settings User session (url and authentication)
     * @return New client, based on the client adapter returned from ClientProvider.
     * @throws ConfigurationStateException 
     */
	public static ReposWorkingCopy getClient(CheckoutSettings settings) {
		ClientProvider clientProvider = getClientProvider();
		ISVNClientAdapter clientAdapter = clientProvider.getSvnClient();
		// need a config area even if it is the default, because it is required for the working copy
		File configurationArea = clientProvider.getDefaultRuntimeConfigurationArea();
		
		ClientConfiguration clientConfiguration;
		try {
			clientConfiguration = clientProvider.getRuntimeConfiguration(configurationArea);
		} catch (ConfigurationStateException e) {
			throw new RuntimeException("The shared configuration for Subversion clients in this user profile is invalid", e);
		}
		
		return newWorkingCopy(settings, clientAdapter, clientConfiguration);
	}
	
	/**
	 * Creates a working copy and a client configuration model for a custom folder
	 * @param settings
	 * @param runtimeConfigurationArea
	 * @return
	 * @throws ConfigurationStateException
	 */
	public static ReposWorkingCopy getClient(CheckoutSettings settings, File runtimeConfigurationArea) throws ConfigurationStateException {
		ClientProvider provider = getClientProvider();
		// need to create the configuration first, because the location should be validated before the client can create contents
		ClientConfiguration clientConfiguration = provider.getRuntimeConfiguration(runtimeConfigurationArea);
		logger.info("Using configuration area {}", runtimeConfigurationArea);
		// create client. if location had not been validated, client could create contents in invalid locations to (SvnKit does not care)
		ISVNClientAdapter clientAdapter = provider.getSvnClient(runtimeConfigurationArea);
		
		return newWorkingCopy(settings, clientAdapter, clientConfiguration);
	}
	
	/**
	 * Instantiates a working copy and sets a {@link ConflictHandler} to it.
	 * @param settings
	 * @param clientAdapter
	 * @param clientConfiguration
	 * @return
	 */
	private static ReposWorkingCopy newWorkingCopy(CheckoutSettings settings, ISVNClientAdapter clientAdapter, ClientConfiguration clientConfiguration) {
		logger.info("Creating new working copy client for path {}", settings.getWorkingCopyFolder());
		ReposWorkingCopySvn wc = new ReposWorkingCopySvn(
				clientAdapter,
				clientConfiguration,
				settings,
				new ConflictHandlerStandard());
		return wc;
	}
	
	/**
	 * Creates the javahl client if available.
	 * Last resort is the SvnKit client, that needs a license for redistribution.
	 * @throws RuntimeException if there is no client library available. This is considered a deployment issue.
	 */
	private static ClientProvider getClientProvider() throws RuntimeException {
		// don't want to run this exception logic more than necessary
		if (clientProvider!=null) return clientProvider;
		// we prefer javahl
		try {
			clientProvider = JavahlClientProvider.getProvider();
			logger.info("Using Javahl client library. See license: http://subversion.tigris.org/license-1.html");
			return clientProvider;
		} catch (ClientNotAvaliableException e) {
			// it can be very picky about the dll version, so it might be that the dll exists but is invalid
			logger.info("Javahl client library is not available. Is the library file 'libsvnjavahl-1' present?");
		}
	    // try the pure java library. it can be installed by simply adding the jar.
		try {
			clientProvider = SvnKitClientProvider.getProvider();
			logger.warn("Using the SvnKit library. For commercial use this requires a license. See http://svnkit.com/.");
			return clientProvider;
		} catch (ClientNotAvaliableException e) {
			logger.info("SvnKit client library is not available. Is 'svnkit.jar' present?");
		}
		// all alternatives failed
	    throw new RuntimeException("There is no SVN client avaliable. Tried Javahl and SvnKit.");
	}

}
