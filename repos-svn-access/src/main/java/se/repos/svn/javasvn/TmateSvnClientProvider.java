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
package se.repos.svn.javasvn;

import org.tigris.subversion.svnant.SvnTask;
import org.tigris.subversion.svnclientadapter.ISVNClientAdapter;
import org.tigris.subversion.svnclientadapter.SVNClientAdapterFactory;
import org.tigris.subversion.svnclientadapter.javahl.JavaSvnClientAdapterFactory;

import se.repos.svn.ClientProvider;
import se.repos.svn.UserCredentials;

/**
 * Initializes and creates a {@link http://tmate.org/svn/ JavaSVN} client.
 *
 * @author Staffan Olsson
 * @since 2006-apr-14
 * @version $Id$
 */
public class TmateSvnClientProvider implements ClientProvider {

	public ISVNClientAdapter getSvnClient() {
		// check and initialize client
        if (!SvnTask.isJavaSVNAvailable()) {
            throw new RuntimeException("JavaSVN is not available");
        }
        
        ISVNClientAdapter svnClient = SVNClientAdapterFactory.createSVNClient(
        		JavaSvnClientAdapterFactory.JAVASVN_CLIENT);
        
        return svnClient;
	}

	public ISVNClientAdapter getSvnClient(UserCredentials login) {
		ISVNClientAdapter svnClient = getSvnClient();
		svnClient.setUsername(login.getUsername());
		svnClient.setPassword(login.getPassword());
		return svnClient;
	}

}
