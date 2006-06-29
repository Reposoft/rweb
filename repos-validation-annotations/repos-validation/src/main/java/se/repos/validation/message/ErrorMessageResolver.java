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
package se.repos.validation.message;

import se.repos.validation.impl.ValidationFailedException;

/**
 * Converts a trown validation exception to a message code that can be localized.
 * 
 * If the error is a validation error from a {@link se.repos.validation.ValidationStrategy}
 * the message is resolved based on the strategy class and any arguments it might have
 * in the constructor, as well as the value that was invalidated.
 * 
 * An implementation of this interface is supposed to be plugged in
 * right before the message reaches the user. The application can never
 * recover from invalid input, so the errors should interrupt operations
 * transparently. The presentation layer will use this service to
 * resolve a message code from any kind of validation error.
 */
public interface ErrorMessageResolver {

	/**
	 * 
	 * @param error
	 * @return message code, arguments and (maybe) default message
	 */
	//MessageSourceResolvable getMessage(IllegalArgumentException error);
	
}
