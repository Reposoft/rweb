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

/**
 * Represents validation of a simple field, according to the rules defined by implementing class.
 * <p>
 * For validation of objects with multiple fields, use Spring's {@see org.springframework.validation.Validator}.
 * </p>
 * 
 * @author Staffan Olsson
 * @since 2005-nov-14
 * @version $Id$
 */
public interface ValidationRule<V> {

    /**
     * @param value The value to validate
     * @throws IllegalArgumentException If the value is not valid
     */
    void validate(V value) throws IllegalArgumentException;

    /**
     * @param value The value to validate
     * @param feieldName The name of the input field where the value came from, to be presented to user
     * @throws IllegalArgumentException If the value is not valid
     * @deprecated This is not the way to handle field names, because the rule must be stateless
     */
    void validate(V value, String fieldName) throws IllegalArgumentException;;
    
    /**
     * @param value value to be validated
     * @return true if valid
     */
    boolean accepts(V value);
    
    /**
     * @param value to be validated
     * @return true if invalid
     */
    boolean rejects(V value);
}
