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
package se.repos.text.convert;

import org.apache.commons.codec.Decoder;
import org.apache.commons.codec.DecoderException;
import org.apache.commons.codec.Encoder;
import org.apache.commons.codec.EncoderException;

import se.repos.text.convert.CamelCase;

import junit.framework.TestCase;

public class CamelCaseTest extends TestCase {

    // codec should be stateless and immutable
    CamelCase codec = new CamelCase();
    
    public void testIsFirstDigitInNumber() {
        StringBuffer s = new StringBuffer("2ab34cd5");
        assertTrue(codec.isFirstDigitInNumber(s, 0));
        assertFalse(codec.isFirstDigitInNumber(s, 1));
        assertFalse(codec.isFirstDigitInNumber(s, 2));
        assertTrue(codec.isFirstDigitInNumber(s, 3));
        assertFalse(codec.isFirstDigitInNumber(s, 4));
        assertFalse(codec.isFirstDigitInNumber(s, 5));
        assertFalse(codec.isFirstDigitInNumber(s, 6));
    }
    
    public void testIsLast() {
        StringBuffer s = new StringBuffer("s");
        assertTrue(codec.isLast(s, 0));
        s.append("t");
        assertFalse(codec.isLast(s, 0));
        assertTrue(codec.isLast(s, 1));
    }
    
    public void testIsStartOfNewWord() {
        StringBuffer s = new StringBuffer("ANewJDKGotAnX");
        assertTrue("first letter always", codec.isStartOfNewWord(s, 0));
        assertTrue(codec.isStartOfNewWord(s, 1));
        assertFalse(codec.isStartOfNewWord(s, 2));
        assertFalse(codec.isStartOfNewWord(s, 3));
        assertTrue(codec.isStartOfNewWord(s, 4));
        assertFalse("continue abbreviation", codec.isStartOfNewWord(s, 5));
        assertFalse("continue abbreviation", codec.isStartOfNewWord(s, 6));
        assertTrue("first word after abbreviation", codec.isStartOfNewWord(s, 7));
        assertTrue(codec.isStartOfNewWord(s, 10));
        assertFalse(codec.isStartOfNewWord(s, 11));
        assertTrue(codec.isStartOfNewWord(s, 12));
    }
    
    public void testIsPartOfAbbreviation() {
        StringBuffer s = new StringBuffer("anOKWordX");
        assertFalse(codec.isCaps(s, 0));
        assertFalse(codec.isCaps(s, 1));
        assertTrue(codec.isCaps(s, 2));
        assertTrue(codec.isCaps(s, 3));
        assertFalse("last uppercase letter followed by lowercase is start of next word", codec.isCaps(s, 4));
        assertFalse(codec.isCaps(s, 5));
        assertTrue("uppercase last letter is abbreviation", codec.isCaps(s, 8));
    }

    public void testIsStartOfNewWordAbbr() {
        StringBuffer s = new StringBuffer("NO");
        assertFalse(codec.isStartOfNewWord(s, 1));
    }    

    public void testIsPartOfAbbreviationAbbr() {
        StringBuffer s = new StringBuffer("oN");
        assertTrue(codec.isCaps(s, 1));
        s.append('O');
        assertTrue(codec.isCaps(s, 2));
    }    

    public void testArticleA() {
        StringBuffer s = new StringBuffer("oAoAWord");
        assertTrue(codec.isStartOfNewWord(s, 1));
        assertFalse(codec.isCaps(s, 1));
        assertFalse(codec.isStartOfNewWord(s, 2));
        assertTrue(codec.isStartOfNewWord(s, 3));
        assertFalse(codec.isCaps(s, 3));
        assertTrue(codec.isStartOfNewWord(s, 4));
        assertFalse(codec.isCaps(s, 4));
    }     
    
    public void testEncode() throws EncoderException {
        assertEquals("TestCase", codec.encode("Test case"));
    }  
    
    public void testDecode() throws DecoderException {
        assertEquals("Test case", codec.decode("TestCase"));
    }

    public void testEncodeNull() throws EncoderException {
        assertNull("Should return null on null input", codec.encode(null));
    }  
    
    public void testDecodeNull() throws DecoderException {
        assertNull("Should return null on null input", codec.decode(null));
    }
    
    public void testDecodeLowerCaseFirst() throws DecoderException {
        assertEquals("test case", codec.decode("testCase"));
    }    
    
    public void testDecodeStringWithAbbreviations() throws DecoderException {
        assertEquals("This is a name that 8 is arguably OK and OK", 
        		codec.decode("ThisIsANameThat8IsArguablyOKAndOK"));
    }    

    public void testEncodeStringWithAbbreviations() throws EncoderException {
        assertEquals("ThisIsANameThat247IsArguablyOKAndOK", 
        		codec.encode("This is a name that 24 7 is arguably OK and OK"));
    }    
    
    /*
     * Test method for 'se.repos.tools.text.CamelCase.encode(Object)'
     */
    public void testEncodeObject() throws EncoderException {
        Object obj = new Object() {
            public String toString() {
                return "Test object";
            }
        };
        assertEquals("TestObject", ((Encoder)codec).encode(obj));
    }

    /*
     * Test method for 'se.repos.tools.text.CamelCase.decode(Object)'
     */
    public void testDecodeObject() throws DecoderException {
        Object obj = new Object() {
            public String toString() {
                return "TestObject";
            }
        };
        assertEquals("Test object", ((Decoder)codec).decode(obj));
    }
    
    public void testEncodeObjectNull() throws EncoderException {
    	assertNull("Should return null on null input", ((Encoder)codec).encode(null));
    }
    
    public void testDecodeObjectNull() throws DecoderException {
    	assertNull("Should return null on null input", ((Decoder)codec).decode(null));
    }

}
