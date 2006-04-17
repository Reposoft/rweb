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

/**
 * Static methods for syntactic manipulation and analysis of user input strings.
 * 
 * @author Staffan Olsson
 * @since 2005-nov-13
 * @version $Id$
 */
public abstract class InputUtils {
    
    /**
     * Normalizes string input.
     * @param input From readable input.
     * @return trimmed string, empty string returned as <code>null</code>.
     * @see String#trim()
     */
    public static final String trim(String input) {
        if (input == null) {
            return null;
        }
        String s = input.trim();
        if (s.length() == 0) {
            return null;
        }
        return s;
    }
    
    /**
     * Counts the number of occurrences of a charater in a string
     * @param haystack the string to search
     * @param needle the char to look for
     * @return the number of times that the char occurs in the string
     */
    public static int countOccurrences(String haystack, char needle) {
        if (haystack == null) {
            return 0;
        }
        int n = 0;
        int p = haystack.indexOf(needle);
        while (p >= 0) {
            n++;
            p = haystack.indexOf(needle, p + 1);
        }
        return n;
    }

}
