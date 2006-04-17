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

import se.repos.validation.message.InputException;
import junit.framework.TestCase;

public class InputExceptionTest extends TestCase {

    /*
     * Test method for 'se.repos.tools.validation.InputException.getMessage()'
     */
    public void testGetMessage() {
        try {
            throw new ThisIsATestException();
        } catch (InputException e) {
            assertEquals("ThisIsATest", e.getCodes()[0]);
            assertEquals("This is a test", e.getMessage());
        } catch (Exception e) {
            fail("Should have thrown a InputException");
        }
    }

    public void testGetMessageIgnorantName() {
        try {
            throw new SomeOtherTest();
        } catch (InputException e) {
            assertEquals("SomeOtherTest", e.getCodes()[0]);
            assertEquals("Some other test", e.getMessage());
        } catch (Exception e) {
            fail("Should have thrown a InputException");
        }
    }    

    public void testGetMessageRejectIf() {
        try {
            throw new RejectInvalidValue();
        } catch (InputException e) {
            assertEquals("InvalidValue", e.getCodes()[0]);
            assertEquals("Invalid value", e.getMessage());
        } catch (Exception e) {
            fail("Should have thrown a InputException");
        }
    }     
    
    public void testToString() {
        InputException e = new ThisIsATestException();
        assertEquals("This is a test", e.getMessage());
        assertEquals("ThisIsATest {0:String=TEST} {1:Long=2}", e.toString());
    }    
    
    private class ThisIsATestException extends InputException {
        private static final long serialVersionUID = 1L;
        public Object[] getArguments() { return new Object[] {new String("TEST"), new Long(2)}; }
    }
    
    private class SomeOtherTest extends InputException {
        private static final long serialVersionUID = 1L;
        public Object[] getArguments() { return new Object[0]; }
    }

    private class RejectInvalidValue extends InputException {
        private static final long serialVersionUID = 1L;
        public Object[] getArguments() { return new Object[0]; }
    }
}
