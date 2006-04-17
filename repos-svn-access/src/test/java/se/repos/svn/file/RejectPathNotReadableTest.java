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
package se.repos.svn.file;

import java.io.File;

import junit.framework.TestCase;

public class RejectPathNotReadableTest extends TestCase {

	/*
	 * Test method for 'se.repos.validation.ValidationRuleDecoratorBase.rejects(V)'
	 */
	public void testRejects() {
		File f = new File(System.getProperty("java.home"));
		assertFalse("Java home path should be readable", 
				new RejectPathNotReadable().rejects(f));
	}

}
