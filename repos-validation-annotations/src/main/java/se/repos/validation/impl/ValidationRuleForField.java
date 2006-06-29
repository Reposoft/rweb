/* Copyright 2006 Optime data Sweden
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

/**
 * Provides field name for validation errors.
 * 
 * If fieldName is set, it is returned.
 * If not, the class tries to resolve the argument
 * from the method that called validate.
 * If there are several arguments to that method
 * it choses the one given by argumentNumber property.
 * If argumentNumber is not set, it choses the
 * argument that matches the validated
 * object's type (if not null).
 * If this can't be determined, it just returns
 * the arguments that it could be.
 * 
 * TODO this functionality should be decorated onto validation rules
 * by the instantiating class
 *
 * @author Staffan Olsson
 * @since 2006-apr-15
 * @version $Id$
 * @deprecated should be decorator
 */
public abstract class ValidationRuleForField<V> extends ValidationRuleBase<V> {

    private String fieldName = null;

    public ValidationRuleForField(String fieldName) {
    	super();
    	this.fieldName = fieldName;
    }

    /**
     * @return The name of the field that was validated.
     */
    protected final String getFieldName() {
        return fieldName;
    }	
	
}
