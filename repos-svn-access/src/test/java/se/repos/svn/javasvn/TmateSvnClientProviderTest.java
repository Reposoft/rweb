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

import java.io.File;

import junit.framework.TestCase;
import se.repos.svn.ClientProvider.ClientNotAvaliableException;
import se.repos.svn.svnkit.TmateSvnClientProvider;

public class TmateSvnClientProviderTest extends TestCase {

	public void testGetRuntimeConfigurationArea() {
		TmateSvnClientProvider clientProvider;
		try {
			clientProvider = new TmateSvnClientProvider();
		} catch (ClientNotAvaliableException e) {
			// the JavaSVN library is not required
			return;
		}
		File area = clientProvider.getRuntimeConfigurationArea();
		System.out.println("JavaSVN retuned config folder: " + area.getAbsolutePath());
		// assuming that the test system has a configuration area
		assertNotNull("Should return configuration area from client lib", area);
		assertTrue("Folder should exist", area.exists() && area.isDirectory());
		assertTrue("Configuration area should contain a 'servers' file", new File(area, "servers").exists());
	}

}
