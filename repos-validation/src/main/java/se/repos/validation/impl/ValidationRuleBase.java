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
package se.repos.validation.impl;

import se.repos.validation.ValidationRule;

/**
 * Thread-safe
 * 
 * TODO the validation rule name should be set on the error,
 * and a default message should be given by the
 * error's tostring method, if the error is a 
 * validation failed exception
 * 
 * @author Staffan Olsson
 * @since 2005-nov-15
 * @version $Id$
 */
public abstract class ValidationRuleBase<V>
        implements ValidationRule<V> {
    
	/**
	 * @param value Not null, to be validated
	 */
	protected abstract boolean rejectsValue(V value);

    /*
     * (non-Javadoc)
     * 
     * @see se.repos.tools.validation.ValidationRule#rejects(V)
     */    
    public boolean rejects(V value) {
    	return value == null || rejectsValue(value);
    }
    
    /*
     * (non-Javadoc)
     * 
     * @see se.repos.tools.validation.ValidationRule#validate(V)
     */
    public void validate(V value) throws IllegalArgumentException {
    	if (value == null) {
    		throw new ValidationNullValueException();
    	}
    	if (rejectsValue(value)) {
    		throw new ValidationFailedException();
    	}
    }

    /**
     * @deprecated fieldName info should be added at instantiation
     */
    public void validate(V value, String fieldName)
            throws IllegalArgumentException {
        throw new UnsupportedOperationException("very deprecated");
    }

    /*
     * (non-Javadoc)
     * 
     * @see se.repos.tools.validation.ValidationRule#accepts(V)
     */
    public final boolean accepts(V value) {
        return !rejects(value);
    }

}
