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
package se.repos.validation;

import se.repos.validation.rule.RuleSet;
import se.repos.validation.rule.ValidationRuleBase;
import junit.framework.TestCase;

public class RuleSetTest extends TestCase {

    private class TestRule1 extends ValidationRuleBase<String> {
        public boolean rejectsValue(String value) {
            return value == null;
        }
        public void validate(String value) throws IllegalArgumentException {
            if (rejectsValue(value)) {
                throw new IllegalArgumentException("TestRule1 rejects");
            }
        }
    }

    private class TestRule2 extends ValidationRuleBase<String> {
        public boolean rejectsValue(String value) {
            return "".equals(value);
        }
        public void validate(String value) throws IllegalThreadStateException {
            if (rejectsValue(value)) {
                throw new IllegalThreadStateException();
            }
        }
    }    
    
    // immutable set
    RuleSet<String> set = new RuleSet<String>(new TestRule1(), new TestRule2());
    
    public void setUp() {
        assertTrue("self check", new TestRule1().rejectsValue(null));
        assertTrue("self check", new TestRule2().rejectsValue(""));
    }
    
    /*
     * Test method for 'se.repos.tools.validation.RuleSet.rejects(V)'
     */
    public void testRejects() {
        assertFalse(set.rejectsValue("t"));
        assertTrue(set.accepts("t"));
        assertFalse(set.accepts(null));
        assertFalse(set.accepts(""));
    }

    /*
     * Test method for 'se.repos.tools.validation.RuleSet.validate(V)'
     */
    public void testValidateV() {
        try {
            set.validate("/");
        } catch (IllegalArgumentException e) {
            fail("Both validation rules should accept the test string");
        }
        try {
            set.validate("");
            fail("TestRule2 should reject this");
        } catch (IllegalThreadStateException e) {
            // expected
        } catch (IllegalArgumentException e) {
            fail("Wrong test rule rejected");
        }
        try {
            set.validate(null);
            fail("TestRule1 should reject this");
        } catch (IllegalArgumentException e) {
            assertEquals(IllegalArgumentException.class, e.getClass());
        }
    }
}
