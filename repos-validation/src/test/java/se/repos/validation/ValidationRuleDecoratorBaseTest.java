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
package se.repos.validation;

import se.repos.validation.rule.ValidationRuleBase;
import se.repos.validation.rule.ValidationRuleDecoratorBase;
import junit.framework.TestCase;

public class ValidationRuleDecoratorBaseTest extends TestCase {

	// rejects strings shorter than 2 chars
	ValidationRule<Object> ruleA = new ValidationRuleBase<Object>() {
		public boolean rejectsValue(Object value) { return value.toString().length() < 2; }
	};
	
	// rejects the number 11
	ValidationRule<Integer> ruleB = new ValidationRuleDecoratorBase<Integer>(ruleA) {
		public boolean rejectsValue(Integer value) { return value == 11; };		
	};
	
	/*
	 * Test method for 'se.repos.validation.ValidationRuleDecoratorBase.rejects(V)'
	 */
	public void testRejects() {
		assertTrue("Null should always be rejected", ruleB.rejects(null));
		assertTrue("Should be rejected by the wrapped rule", ruleB.rejects(0));
		assertTrue("Should be rejected by the decorating rule", ruleB.rejects(11));
		assertFalse("Should not be rejected by any rule", ruleB.rejects(10));
	}

	/*
	 * Test method for 'se.repos.validation.ValidationRuleBase.validate(V)'
	 */
	public void testValidateV() {
		try {
			ruleB.validate(0);
			ruleB.validate(11);
		} catch (IllegalArgumentException e) {
			// expected
		}
		ruleB.validate(10);
	}

}
