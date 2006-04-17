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
package se.repos.svnlist.service;

import java.util.Collection;

import org.junit.Test;

import se.repos.svnlist.service.files.RepositoryEntry;
import static org.junit.Assert.*;

import junit.framework.JUnit4TestAdapter;

public class ProjectContentsImplIntegrationTest {

	ProjectContentsImpl projectContents = null;

	public static junit.framework.Test suite() {
	   return new JUnit4TestAdapter(ProjectContentsImplIntegrationTest.class);
	}	
	
	public ProjectContentsImplIntegrationTest() {
		projectContents = new ProjectContentsImpl();
	}
	
	@Test
	public void testGetUrl() {
		projectContents.setUrl("http://svn.collab.net/repos/svn");
		assertEquals("http://svn.collab.net/repos/svn", projectContents.getUrl());
	}
	
	@Test(expected=IllegalArgumentException.class)
	public void testSetUrlInvalid() {
		projectContents.setUrl("http://svn.collab.net/repos/svn/");
		
	}
	
	@Test 
	public void testSvnList() throws Exception {
		Collection<RepositoryEntry> list = projectContents.getList("/");
		assertTrue("Should contain at least trunk, branches adn tags", list.size() > 2);
		for (RepositoryEntry e : list) {
			String name = e.getDescription();
			assertEquals("According to spring Resource, toString should be name", name, e.toString());
			assertNotNull(e.getURL());
			assertTrue("URL should contain the name", e.getURL().toString().indexOf(name) >= 0);
		}
	}

}
