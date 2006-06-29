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
package se.repos.validation.message;

import se.repos.validation.message.InputValidation;

import junit.framework.TestCase;

/**
 * @deprecated
 *
 * @author Staffan Olsson
 * @since 2006-apr-16
 * @version $Id$
 */
public class InputValidationTest extends TestCase {

    InputValidation rule = null;
    
    private class RejectLooksLikeOK extends InputValidation {
        private static final long serialVersionUID = 1L;
        public boolean rejectsValue(Object value) {
            if ("OK".equals(value)) {
                return false;
            }
            return true;
        }
        public Object[] getArguments() {
            return new Object[] {"OK"};
        }
        public boolean supports(Class cls) {
            return String.class.isAssignableFrom(cls);
        }
		public boolean rejects(Object value) {
			if (true) {
				throw new UnsupportedOperationException("Method RejectLooksLikeOK#rejects not implemented yet.");
			}
			return false;
		}        
    }
    
    protected void setUp() throws Exception {
        super.setUp();
        rule = new RejectLooksLikeOK();
    }
    
    // helpers to access protected fields
    private String getStoredFieldName() {
        return ((InputValidation) rule).getFieldName();
    } 
    private Object getStoredValue() {
        return ((InputValidation) rule).getValue();
    }

    /*
     * Test method for 'se.repos.tools.validation.InputValidation.supports(Class)'
     */
    public void testSupports() {
        assertTrue(rule.supports(String.class));
        assertFalse(rule.supports(Object.class));
    }    
    
    /*
     * Test method for 'se.repos.tools.validation.InputValidation.getArguments()'
     */
    public void testToString() {
        String s = rule.toString();
        assertTrue(s.indexOf("LooksLikeOK") >= 0);
        assertTrue("The argument should be in string", s.indexOf("OK") >= 0);
        assertTrue("The argument type should be in the string", s.indexOf("String") >= 0);
    }

    public void testRejects() {
        assertTrue("Test setup should return true", rule.rejectsValue(null));
    }

    public void testAccepts() {
        assertFalse("Should invert rejects", rule.accepts(null));
    }

    public void testValidateV() {
        rule.validate("OK");
        assertEquals("no validation error yet, value should be null", null, getStoredValue());
        try {
            rule.validate("o");
            fail("Should have been rejected with an exception");
        } catch (IllegalArgumentException e) {
            // expected
        }
        assertEquals("validation error thrown, should carry value", "o", getStoredValue());
        try {
            rule.validate(null);
            fail("Should not accept another validation once thrown");
        } catch (RuntimeException e) {
            assertEquals(IllegalStateException.class, e.getClass());
        }
    }

    public void testValidateWithFieldName() {
        assertEquals("field name should null until validation error", null, getStoredFieldName());
        try {
            rule.validate(null, "testField");
            fail("Should have been rejected with an exception");
        } catch (IllegalArgumentException e) {
            // expected
        }
        assertEquals("field name should be saved at validation error", "testField", getStoredFieldName());
    }

    public void testValidateVStringOK() {
        try {
            rule.validate("OK", "testField");
        } catch (IllegalArgumentException e) {
            fail("Should be valid");
        }
        String field = getStoredFieldName();
        assertEquals("field name should not be set if field is valid", null, field);
    }

//    public void testValidateToErrors() {
//        Errors e = new BindException("target", "testField");
//        rule.validate("invalid value", e);
//        assertEquals("the validation error should be added", 1, e.getErrorCount());
//        assertEquals("has no field name so it must be a global error", 1, e.getGlobalErrorCount());
//        ObjectError error = e.getGlobalError();
//        assertEquals("LooksLikeOK", error.getCode());
//        assertEquals("Arguments should be copied", rule.getArguments()[0], error.getArguments()[0]);
//        assertEquals("Looks like OK", error.getDefaultMessage());
//    }
}
