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
import se.repos.svn.UserCredentials;
import se.repos.svn.config.ClientConfiguration;
import se.repos.svn.config.ConfigurationStateException;
import se.repos.svn.config.RuntimeConfigurationArea;

/**
 * Initializes and creates a {@link http://tmate.org/svn/ JavaSVN} client.
 *
 * Note that there should be no compile time references to the tmate classes.
 *
 * @author Staffan Olsson
 * @since 2006-apr-14
 * @version $Id$
 */
public class TmateSvnClientProvider implements ClientProvider {

	public TmateSvnClientProvider() throws ClientNotAvaliableException {
		try {
            SvnKitClientAdapterFactory.setup();
        } catch (SVNClientException e) {
        	throw new ClientNotAvaliableException("Tmate JavaSVN is not available or is already registered.", e);
        }
	}
	
	public ISVNClientAdapter getSvnClient() { 
        ISVNClientAdapter svnClient;
        try {
        	svnClient = SVNClientAdapterFactory.createSVNClient(SvnKitClientAdapterFactory.SVNKIT_CLIENT);
        } catch (NoClassDefFoundError e) {
        	throw new RuntimeException("The JavaSVN library is not present. Add 'javasvn.jar' to classpath and retry.");
        }
        if (svnClient == null) {
        	throw new RuntimeException("There is no SVN client available.");
        }
        return svnClient;
	}

	public ISVNClientAdapter getSvnClient(UserCredentials login) {
		ISVNClientAdapter svnClient = getSvnClient();
		svnClient.setUsername(login.getUsername());
		svnClient.setPassword(login.getPassword());
		return svnClient;
	}

	public File getRuntimeConfigurationArea() {
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
		if (folder==null) throw new RuntimeException("Could not get configuration area folder from JavaSVN");
		return (File) folder;
	}
	
	/**
	 * Creates default repos-svn-access configuration instance
	 */
	public ClientConfiguration getRuntimeConfiguration() throws ConfigurationStateException {
		return new RuntimeConfigurationArea();
	}	

}
