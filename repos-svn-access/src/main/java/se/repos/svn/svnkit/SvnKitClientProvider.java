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
package se.repos.svn.svnkit;

// no imports from org.tmatesoft
import java.io.File;
import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;

import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientAdapterFactory;
import org.tigris.subversion.svnclientadapter.SVNClientException;
import org.tigris.subversion.svnclientadapter.svnkit.SvnKitClientAdapterFactory;

import se.repos.svn.ClientProvider;
import se.repos.svn.config.ClientConfiguration;
import se.repos.svn.config.ConfigurationStateException;
import se.repos.svn.config.RuntimeConfigurationArea;

/**
 * Initializes and creates a {@link http://svnkit.com/ SvnKit} client.
 *
 * Note that there should be no compile time references to the SvnKit classes, due to licencing issues.
 *
 * @author Staffan Olsson
 * @since 2006-apr-14
 * @version $Id$
 */
public class SvnKitClientProvider implements ClientProvider {

	private static SvnKitClientProvider provider = null;
	
	/**
	 * Factory method, because SvnKit needs a singleton provider
	 * @return A ready to use svn client factory
	 * @throws ClientNotAvaliableException if the client library is can not be initialize
	 */
	public static SvnKitClientProvider getProvider() throws ClientNotAvaliableException {
		// avoid org.tigris.subversion.svnclientadapter.SVNClientException: factory for type svnkit already registered
		if (provider != null) return provider;
		provider = new SvnKitClientProvider();
		return provider;
	}	
	
	private SvnKitClientProvider() throws ClientNotAvaliableException {
		try {
            SvnKitClientAdapterFactory.setup();
        } catch (SVNClientException e) {
        	throw new ClientNotAvaliableException("SvnKit is not available or is already registered.", e);
        }
	}
	
	public ISVNClientAdapter getSvnClient() { 
        ISVNClientAdapter svnClient;
        try {
        	svnClient = SVNClientAdapterFactory.createSVNClient(SvnKitClientAdapterFactory.SVNKIT_CLIENT);
        } catch (NoClassDefFoundError e) {
        	throw new RuntimeException("The SvnKit library is not present. Add 'svnkit.jar' to classpath and retry.");
        }
        if (svnClient == null) {
        	throw new RuntimeException("There is no SVN client available.");
        }
        return svnClient;
	}

	public ISVNClientAdapter getSvnClient(File configFolder) {
		ISVNClientAdapter svnClient = getSvnClient();
		try {
			svnClient.setConfigDirectory(configFolder); // also creates the contents if they do not exist
		} catch (SVNClientException e) {
			throw new RuntimeException("Subversion client did not accept default configuration area " + configFolder, e);
		}
		return svnClient;
	}

	public File getDefaultRuntimeConfigurationArea() {
		Class tmateFileUtil;
		try {
			tmateFileUtil = Class.forName("org.tmatesoft.svn.core.wc.SVNWCUtil");
		} catch (ClassNotFoundException e) {
			// TODO auto-generated
			throw new RuntimeException("ClassNotFoundException thrown, not handled", e);
		}
		Method m;
		try {
			m = tmateFileUtil.getMethod("getDefaultConfigurationDirectory", new Class[]{});
		} catch (SecurityException e) {
			// TODO auto-generated
			throw new RuntimeException("SecurityException thrown, not handled", e);
		} catch (NoSuchMethodException e) {
			// TODO auto-generated
			throw new RuntimeException("NoSuchMethodException thrown, not handled", e);
		}
		Object folder;
		try {
			folder = m.invoke(null, new Object[]{});
		} catch (IllegalArgumentException e) {
			// TODO auto-generated
			throw new RuntimeException("IllegalArgumentException thrown, not handled", e);
		} catch (IllegalAccessException e) {
			// TODO auto-generated
			throw new RuntimeException("IllegalAccessException thrown, not handled", e);
		} catch (InvocationTargetException e) {
			// TODO auto-generated
			throw new RuntimeException("InvocationTargetException thrown, not handled", e);
		}
		if (folder==null) throw new RuntimeException("Could not get configuration area folder from SvnKit");
		return (File) folder;
	}
	
	/**
	 * Creates default repos-svn-access configuration instance
	 */
	public ClientConfiguration getRuntimeConfiguration(File configurationArea) throws ConfigurationStateException {
		return new RuntimeConfigurationArea(configurationArea);
	}

}