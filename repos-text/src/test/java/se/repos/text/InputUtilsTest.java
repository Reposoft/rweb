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
package se.repos.text;

import junit.framework.TestCase;

import static se.repos.text.InputUtils.*;

public class InputUtilsTest extends TestCase {

    /*
     * Test method for 'se.repos.tools.text.InputUtils.trim(String)'
     */
    public void testTrim() {
        assertEquals("test", trim("test"));
        assertEquals("a", trim("a "));
        assertEquals("a", trim(" a"));
        assertEquals("a", trim("  a  "));
        assertEquals("a b", trim(" a b "));
        assertEquals(null, trim(null));
        assertEquals(null, trim(""));
    }
    
    public void testCountOccurrences() {
        assertEquals(1, countOccurrences("test", 's'));
        assertEquals(2, countOccurrences("test", 't'));
        assertEquals(3, countOccurrences("///", '/'));
        assertEquals(0, countOccurrences(null, 'a'));
    }

    /*
     * Test method for 'se.repos.tools.text.InputUtils.rejectIfEmpty(String)'

    public void testRejectIfEmpty() {
        try {
            rejectIfEmpty("");
            fail("empty string should be rejected");
        } catch (StringIsEmptyException e) {
            // expected
        }
    }

    public void testRejectIfEmptyNull() {
        try {
            rejectIfEmpty(null);
            fail("null should be rejected");
        } catch (StringIsEmptyException e) {
            // expected
        }
    }    

    public void testRejectIfEmptySpaces() {
        try {
            rejectIfEmpty("  ");
            fail("spaces only should be rejected");
        } catch (StringIsEmptyException e) {
            // expected
        }
    }

    public void testRejectIfEmptyTabNewline() {
        try {
            rejectIfEmpty("\t\n");
            fail("tabs and newlines count as spaces should be rejected");
        } catch (StringIsEmptyException e) {
            // expected
        }
    }    
    
    public void testRejectIfEmptyNot() {
        rejectIfEmpty(" . ");
        rejectIfEmpty("\"");
        rejectIfEmpty("null");
        rejectIfEmpty("\n_");
    }
    
  
    
    public void testRejectIfEndsWith() {
        try {
            rejectIfEndsWith("test", 't');
            fail("Should reject");
        } catch (StringEndsWithIllegalCharacterException e) {
            // expected
        }
    }

    public void testRejectIfEndsWithSlash() {
        try {
            rejectIfEndsWith("test/", '/');
            fail("Should reject");
        } catch (StringEndsWithIllegalCharacterException e) {
            // expected
        }
    }    
    
    public void testRejectIfEndsWithNot() {
        rejectIfEndsWith("test", 'T');
        rejectIfEndsWith("test", 's');
        rejectIfEndsWith("test ", 't');
        rejectIfEndsWith("/dir", '/');
    }
    
    public void testRejectIfNotStartsWithNo() {
        try {
            rejectIfNotStartsWith("test", '/');
            fail("Should reject");
        } catch (StringStartMismatchException e) {
            // expected
        }
    }

    public void testRejectIfNotStartsWithNull() {
        try {
            rejectIfNotStartsWith(null, '\n');
            fail("Should reject all null input");
        } catch (StringStartMismatchException e) {
            // expected
        }
    }    

    public void testRejectIfNotStartsWithEmpty() {
        try {
            rejectIfNotStartsWith("", '\t');
            fail("Should reject all empty input");
        } catch (StringStartMismatchException e) {
            // expected
        }
    }    
    
    public void testRejectIfNotStartsWith() {
        rejectIfNotStartsWith("test", 't');
        rejectIfNotStartsWith("/p", '/');
        rejectIfNotStartsWith(" t", ' ');
    }
     */
}
