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
package se.repos.validation.strings;

import junit.framework.TestCase;
import se.repos.validation.impl.ValidationFailedException;
import se.repos.validation.strings.RejectStringContentsNotAllowed;

import org.junit.Test;

public class RejectStringContentsNotAllowedTest extends TestCase {
	
    @Test public void testRejectIfContainsNot() {
        new RejectStringContentsNotAllowed('/').validate("name");
        new RejectStringContentsNotAllowed('A').validate("name");
        new RejectStringContentsNotAllowed('\n').validate("");
        new RejectStringContentsNotAllowed('\n').validate(" ");
        new RejectStringContentsNotAllowed('\t').validate(" \n");
    }
    
    @Test public void testRejectsNull() {
    	assertTrue("Should always reject null", new RejectStringContentsNotAllowed(' ').rejects(null));
    }
    
    @Test public void testRejectIfContains() {
        try {
            new RejectStringContentsNotAllowed('a').validate("name");
            fail("Should reject");
        } catch (ValidationFailedException e) {
            // expected
        }
    }    

    @Test public void testRejectIfContainsSlash() {
        try {
            new RejectStringContentsNotAllowed('/').validate("/");
            fail("Should reject");
        } catch (ValidationFailedException e) {
            // expected
        }
    }
    
    @Test public void testRejectIfContainsSpace() {
        try {
            new RejectStringContentsNotAllowed(' ').validate("name ");
            fail("Should reject");
        } catch (ValidationFailedException e) {
            // expected
        }
    }

    @Test public void testRejectIfContainsNewline() {
        try {
            new RejectStringContentsNotAllowed('\n').validate("\nname ");
            fail("Should reject");
        } catch (ValidationFailedException e) {
            // expected
        }
    }  
}
