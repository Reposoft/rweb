/* Copyright 2005 Optime data Sweden
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
package se.repos.validation.impl;

import se.repos.validation.ValidationRule;
import se.repos.validation.impl.ValidationNullValueException;
import se.repos.validation.impl.ValidationRuleBase;
import junit.framework.TestCase;

public class ValidationRuleBaseTest extends TestCase {

    ValidationRule<String> rule;
    
    protected void setUp() throws Exception {
        super.setUp();
        rule = new ValidationRuleBase<String>() {
            public boolean rejectsValue(String value) {
            	// this subclass accepts 'OK' and, if not stopped by superclass, strangely accepts null
                return value != null && !"OK".equals(value);
            }
        };
    }

    public void testRejectsNullValue() {
    	assertTrue("Null values should always be rejected (can't be validated)", rule.rejects(null));
    }
    
    public void testDoesNotAcceptNullValue() {
    	assertFalse("Null values are never accepted (can't be validated)", rule.accepts(null));
    }
    
    public void testVaidateNullValue() {
    	try {
    		rule.validate(null);
    		fail("Should throw validation error");
    	} catch (ValidationNullValueException ne) {
    		// expected
    	} catch (Exception e) {
    		fail("Should throw " + ValidationNullValueException.class.getSimpleName());
    	}
    }
    
    public void testRejects() {
        assertTrue("Test setup should return true", rule.rejects("not ok"));
    }

    public void testAccepts() {
        assertFalse("Should invert rejects", rule.accepts("not ok"));
    }

    public void testValidateV() {
        try {
            rule.validate("not ok");
            fail("Should have been rejected with an exception");
        } catch (IllegalArgumentException e) {
            // expected
        }
    }

}
