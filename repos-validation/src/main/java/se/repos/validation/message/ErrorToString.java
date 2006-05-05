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

import se.repos.validation.rule.ValidationFailedException;

/**
 * Text to use when an error is logged or displayed to user without a prepared translation.
 *
 * @author Staffan Olsson
 * @since 2006-apr-16
 * @version $Id$
 */
public interface ErrorToString {

	String getMessage(ValidationFailedException error);
	
}
