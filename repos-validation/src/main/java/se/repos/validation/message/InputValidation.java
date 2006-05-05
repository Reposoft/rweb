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

import org.springframework.validation.Errors;
import org.springframework.validation.Validator;

import se.repos.validation.ValidationRule;
import se.repos.validation.rule.ValidationRuleBase;

/**
 * Self contained validation rule and error processing.
 * 
 * Impelentaton {@see se.repos.tools.validation.ValidationRule} that is Throwable, and therefore can't use generics.
 * 
 * Throw a clone of this class as exception.
 * 
 * Store field name (unlike in validationRuleBase)
 * 
 * Be able to produce error compatible with spring errors.
 * Maybe a method to add as error to Errors.
 * 
 * @author Staffan Olsson
 * @since 2005-nov-14
 * @version $Id$
 * @see se.repos.validation.rule.ValidationRuleBase
 */
public abstract class InputValidation extends InputException 
        implements ValidationRule<Object>, Validator {

    private String fieldName = null;
    private Object value = null;
    
    /**
     * For validation rules that need parameters, add them as constructor arguments.
     */
    public InputValidation() {
        super();
    }
    
    /**
     * @return The arguments for the error message, should normally include the validation parameters followed by the invalid value.
     * @see se.repos.validation.message.InputException#getArguments()
     */
    public abstract Object[] getArguments();


    /* (non-Javadoc)
     * @see org.springframework.validation.Validator#supports(java.lang.Class)
     */
    public abstract boolean supports(Class cls);
    
    public abstract boolean rejectsValue(Object value);
    
    public boolean accepts(Object value) {
        return !rejectsValue(value);
    }
    
    /* (non-Javadoc)
     * @see se.repos.tools.validation.ValidationRule#validate(V)
     */
    public void validate(Object value) throws IllegalArgumentException {
        if (this.value != null) {
            throw new IllegalStateException("value!=null, already an error");
        }
        if (rejectsValue(value)) {
            this.value = value;
            throw this;
        }
    }

    /* (non-Javadoc)
     * @see se.repos.tools.validation.ValidationRule#validate(V, java.lang.String)
     */
    public final void validate(Object value, String fieldName)
            throws IllegalArgumentException {
        if (this.fieldName != null) {
            throw new IllegalStateException("fieldName!=null, already an error");
        }
        this.fieldName = fieldName;
        validate(value);
        this.fieldName = null;
    }
    
    public void validate(Object value, Errors errors) {
        if (rejectsValue(value)) {
            store(errors);
        }
    }

    /**
     * Stores this error in an Errors collection.
     * @param errors to copy this error to, as a field error if fieldName is set.
     */
    public void store(Errors errors) {
        if (fieldName != null) {
            errors.rejectValue(fieldName, getMessageCode(), getArguments(), getMessage());
        } else {
            errors.reject(getMessageCode(), getArguments(), getMessage());
        }
    }

    /**
     * @return Returns the fieldName.
     */
    protected String getFieldName() {
        return fieldName;
    }

    /**
     * @return The invalid value, or null if this 
     */
    protected Object getValue() {
        return value;
    }

}
