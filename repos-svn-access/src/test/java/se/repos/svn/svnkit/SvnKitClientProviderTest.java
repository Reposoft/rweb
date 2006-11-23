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

import java.io.File;

import junit.framework.TestCase;
import se.repos.svn.ClientProvider;
import se.repos.svn.ClientProvider.ClientNotAvaliableException;
import se.repos.svn.config.RuntimeConfigurationArea;
import se.repos.svn.svnkit.SvnKitClientProvider;
import se.repos.svn.test.TestFolder;

public class SvnKitClientProviderTest extends TestCase {

	public void testGetRuntimeConfigurationArea() {
		SvnKitClientProvider clientProvider;
		try {
			clientProvider = SvnKitClientProvider.getProvider();
		} catch (ClientNotAvaliableException e) {
			// the SvnKit library is not required
			return;
		}
		File area = clientProvider.getDefaultRuntimeConfigurationArea();
		System.out.println("SvnKit retuned config folder: " + area.getAbsolutePath());
		// assuming that the test system has a configuration area
		assertNotNull("Should return configuration area from client lib", area);
		assertTrue("Folder should exist", area.exists() && area.isDirectory());
		assertTrue("Configuration area should contain a 'servers' file", new File(area, "servers").exists());
		
		// here we have a chance to test our own method for this
		// (it is not required that they give the same result, but there is currently no good reason why not)
		File ourown = RuntimeConfigurationArea.getDefaultConfigFolder();
		assertEquals("Checking the SvnKit method for resoling config folder agains our own", area, ourown);
	}
	
	public void testCreateDefaultConfiguration() {
		
		File configFolder = TestFolder.getNew();
		configFolder.delete(); // can't have an empty config folder
		
		ClientProvider provider;
		try {
			provider = SvnKitClientProvider.getProvider();
		} catch (ClientNotAvaliableException e) {
			// OK, can't test this if javahl is not available
			e.printStackTrace();
			System.out.println("Can not test SvnKit client creation, because SvnKit is not available. " + e);
			return;
		}
		
		assertFalse("This test should start with a non-existing config area folder", configFolder.exists());
		provider.getSvnClient(configFolder); // calls setConfigDir
		assertTrue("After the client is initialized with a custom folder, contents should have been created", configFolder.exists());
		
		assertTrue("Should find 'config' file", new File(configFolder, "config").exists());
		assertTrue("Should find 'servers' file", new File(configFolder, "servers").exists());
	}

}
