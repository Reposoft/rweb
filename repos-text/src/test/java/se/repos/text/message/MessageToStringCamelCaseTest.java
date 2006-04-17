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
package se.repos.text.message;

import org.springframework.context.MessageSourceResolvable;
import org.springframework.context.support.DefaultMessageSourceResolvable;

import junit.framework.TestCase;

public class MessageToStringCamelCaseTest extends TestCase {

	MessageToString decoder = new MessageToStringCamelCase();
	
	/*
	 * Test method for 'se.repos.text.message.MessageToStringCamelCase.getMessage(MessageInfo)'
	 */
	public void testGetMessageOnlyCode() {
		MessageSourceResolvable msg = new DefaultMessageSourceResolvable("someError");
		assertEquals("some error", decoder.getMessage(msg));
	}

	public void testGetMessageCodeAndOneArg() {
		MessageSourceResolvable msg = 
			new DefaultMessageSourceResolvable(
					new String[] {"someError"}, 
					new Object[] {3});
		assertEquals("some error: 3", decoder.getMessage(msg));
	}
	
	public void testGetMessageCodeAndTwoArgs() {
		MessageSourceResolvable msg = 
			new DefaultMessageSourceResolvable(
					new String[] {"someError"}, 
					new Object[] {3, "aField"});
		assertEquals("some error: 3, aField", decoder.getMessage(msg));
	}
	
}
