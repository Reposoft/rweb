/* Copyright 2006 Optime data Sweden
 */
package se.repos.svn;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientException;

import se.repos.svn.config.ClientConfiguration;
import se.repos.svn.config.ConfigurationStateException;

/**
 * Represents a choice of svn client library, and the initialization logic for it.
 * 
 * A client created with {@link #getSvnClient()} uses the default Subversion
 * configuration for the user profile. It is possible, but not at all nessecary,
 * to see the configuration of that profile by using {@link #getDefaultRuntimeConfigurationArea()}
 * and {@link #getRuntimeConfiguration(File)}.
 * <p>
 * It is also possible to use a custom client configuration, by creating a File
 * instance pointing to a folder (non-existing or existing with configuration files)
 * and then use {@link #getSvnClient(File)} and {@link #getRuntimeConfiguration(File)}.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo add handling of client library not available, for example an exception
 */
public interface ClientProvider {
	
	/**
	 * Provides an initialized svnClient to the application.
	 * 
	 * This operation does the setup of the client,
	 * so it should be called once and kept throughout the user's work session.
	 * <p>
	 * The client will get the default configuration area location, and it is
	 * assumed that it can create default contents if that location does not exist.
	 *
	 * @return A client ready to do svn operations. Never returns null.
	 * @throws Runtime exception if the client can not be started.
	 * The constructor should throw checked exception if this can be foreseen.
	 */
	ISVNClientAdapter getSvnClient();
	
	/**
	 * Provides an initialized svnClient with a specified runtime configuration.
	 * <p>
	 * This method also makes sure that default configuration is written to the folder
	 * if it does not already exist.
	 *
	 * @param runtimeConfigurationArea The location of the subversion client confg files.
	 * @return A client ready to do svn operations, with the specified configuration.
	 * @see #getRuntimeConfiguration(File) for the corresponding configuration model.
	 */
	ISVNClientAdapter getSvnClient(File runtimeConfigurationArea);
	
	/**
	 * Supplies a read-write model of the runtime configuration area for the client.
	 * 
	 * Applications trust that changing this configuration affects the behaviours of the initialized clients.
	 * <p>
	 * The funny thing with ISVNClientAdapter is that you can
	 * {@link ISVNClientAdapter#setConfigDirectory(java.io.File) setConfigDirectory}
	 * but not read the setting.
	 * Thus, to change configuration directory it must be set in the client adapter,
	 * and at the same time reflected with a new ClientConfiguration.
	 * The preferred way is to initiali<ze the client with a custom configuratoin,
	 * and then never change it. If it must be changed, the existing client instance
	 * must be updated at the same time as a new configuration is retrieved from this method.
	 * 
	 * @return The configuration for the subversion client
	 */
	ClientConfiguration getRuntimeConfiguration(File runtimeConfigurationArea) throws ConfigurationStateException;
	
	/**
	 * @return The folder that a new svn client from this provider will use for client configuration by default
	 */
	File getDefaultRuntimeConfigurationArea();
	
	/**
	 * Exception thrown by initializer if the client can not be created,
	 * for example if the library is not available.
	 */
	public static class ClientNotAvaliableException extends Exception {
		private static final long serialVersionUID = 1L;
		public ClientNotAvaliableException(SVNClientException e) {
			super(e);
		}
		public ClientNotAvaliableException(String message, SVNClientException e) {
			super(message, e);
		}
	}
	
}
