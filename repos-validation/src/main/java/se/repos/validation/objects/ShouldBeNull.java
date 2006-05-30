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
package se.repos.validation.objects;

import se.repos.validation.ValidationRule;
import se.repos.validation.impl.ValidationFailedException;

/**
 * Because all validation rules expect the object to be not-null, 
 * this is here to validate in the rare condition that a variable should be null.
 * 
 * Can be used for example when composing a validator for the state of a bean.
 *
 * @author Staffan Olsson
 * @since 2006-apr-16
 * @version $Id$
 */
public class ShouldBeNull implements ValidationRule<Object> {

	public void validate(Object value) throws IllegalArgumentException {
		if (value == null) {
			return;
		}
		throw new ValidationFailedException();
	}

	public boolean accepts(Object value) {
		return !rejects(value);
	}

	public boolean rejects(Object value) {
		return value != null;
	}
	
	public void validate(Object value, String fieldName)
			throws IllegalArgumentException {
		if (true) {
			throw new UnsupportedOperationException(
					"Method ShouldBeNull#validate is deprecated.");
		}
	}

}
