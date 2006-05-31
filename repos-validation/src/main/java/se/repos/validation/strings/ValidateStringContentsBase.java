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

import se.repos.validation.ValidationResult;
import se.repos.validation.ValidationStrategy;

/**
 * Validates on the existance or lack of a specified substring in input.
 * @author Staffan Olsson
 * @since 2005-nov-18
 * @version $Id$
 */
public abstract class ValidateStringContentsBase implements
		ValidationStrategy<String> {

    private String substring;
    
    /**
     * @param the substring to look for
     */
    public ValidateStringContentsBase(String substring) {
        super();
        this.substring = substring;
    }

    /**
     * @param the character to look for as substring
     */
    public ValidateStringContentsBase(char substring) {
        super();
        this.substring = Character.toString(substring);
    }    
    
    /**
     * @return the string to look for
     */
    protected String getSubstring() {
        return substring;
    }

    public ValidationResult validate(String value) {
    	return rejectsValue(value) ? ValidationResult.INVALID : ValidationResult.VALID;
    }
    
    /**
     * This method is named like this only to make all old subclasses still valid
     * @param value
     * @return
     */
    protected abstract boolean rejectsValue(String value);
}
