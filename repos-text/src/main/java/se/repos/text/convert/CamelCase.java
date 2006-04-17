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

import org.apache.commons.codec.DecoderException;
import org.apache.commons.codec.EncoderException;
import org.apache.commons.codec.StringDecoder;
import org.apache.commons.codec.StringEncoder;
import static java.lang.Character.isDigit;
import static java.lang.Character.isLetter;
import static java.lang.Character.isWhitespace;
import static java.lang.Character.isUpperCase;
import static java.lang.Character.isLowerCase;
import static java.lang.Character.toUpperCase;
import static java.lang.Character.toLowerCase;

/**
 * Encodes and decodes CamelCase with uppercase first letter.
 * <p>
 * Abbreviations are, like all words, followed with an uppercase letter, for example OKOrCancel.
 * <p>
 * An abbreviation ({@see #isCaps(StringBuffer, int)}) is always found in a sequence of at least three
 * uppercase characters. The only exception is in the end of the string, where one or two uppercase letters
 * will be the same both encoded and decoded.
 * @author Staffan Olsson
 * @since 2005-nov-13
 * @version $Id$
 * @link http://en.wikipedia.org/wiki/CamelCase
 */
public final class CamelCase
    implements StringEncoder, StringDecoder {

    /**
     * The space character when unpacking CamelCase to nnormal text
     */
    public static final char WORD_SEPARATOR = ' ';
    
    public String encode(String normal) throws EncoderException {
    	if (normal == null) {
    		return null;
    	}
        StringBuffer s = new StringBuffer(normal);
        int p = -1;
        while (++p < s.length()) {
            if (isWhitespace(s.charAt(p))) {
                s.deleteCharAt(p);
                upper(s, p);
            }
        }
        return s.toString();
    }
    
    public String decode(String camel) throws DecoderException {
    	if (camel == null) {
    		return null;
    	}
        StringBuffer s = new StringBuffer(camel);
        int p = 0; // first character is always uppercase
        while (++p < s.length()) {
            if (isStartOfNewWord(s, p)) {
                s.insert(p++, WORD_SEPARATOR);
                if (p > 0 && !isCaps(s, p)) {
                    lower(s, p);
                }
            }
        }
        return s.toString();
    }

    boolean isLast(StringBuffer s, int p) {
        return s.length() - 1 == p;
    }
    
    boolean isStartOfNewWord(StringBuffer s, int p) {
        if (p == 0 || isFirstDigitInNumber(s, p)) {
            return true;
        }
        if (isLast(s, p) && isUpperCase(s.charAt(p - 1))) {
            return false;
        }
        return (isUpperCase(s.charAt(p)) 
                    && (isLast(s, p) || isLowerCase(s.charAt(p - 1)) || isLowerCase(s.charAt(p + 1))));
    }
    
    boolean isFirstDigitInNumber(StringBuffer s, int p) {
        return isDigit(s.charAt(p)) && (p == 0 || !isDigit(s.charAt(p - 1)));
    }
    
    boolean isCaps(StringBuffer s, int p) {
        return isUpperCase(s.charAt(p)) && (isLast(s, p) || isUpperCase(s.charAt(p + 1)))
            && ((isLast(s, p) || isLast(s, p + 1) || isUpperCase(s.charAt(p + 2))) || (p == 0 || isUpperCase(s.charAt(p - 1))));
    }

    /**
     * @param s The buffer to mutate
     * @param pos The position
     */
    void upper(StringBuffer s, int pos) {
        if (isLetter(s.charAt(pos))) {
            s.setCharAt(pos, toUpperCase(s.charAt(pos)));
        }
    }

    /**
     * @param s The buffer to mutate
     * @param pos The position
     */    
    void lower(StringBuffer s, int pos) {
        if (isLetter(s.charAt(pos))) {
            s.setCharAt(pos, toLowerCase(s.charAt(pos)));
        }
    }

    public Object encode(Object normal) throws EncoderException {
    	if (normal == null) {
    		return null;
    	}
        return encode(normal.toString());
    }

    public Object decode(Object camel) throws DecoderException {
    	if (camel == null) {
    		return null;
    	}
        return decode(camel.toString());
    }
}
