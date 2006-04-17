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
package se.repos.validation;

public abstract class ValidationRuleDecoratorBase<V> extends ValidationRuleBase<V> {

	ValidationRule<? super V> wrappedRule;
	
	protected ValidationRuleDecoratorBase(ValidationRule<? super V> wrappedRule) {
		this.wrappedRule = wrappedRule;
	}
	
	/**
	 * @param value that was valid according to the wrapped rule
	 * @return true if the value should be rejected with this rule
	 */
	protected abstract boolean rejectsValue(V value);
	
	@Override
	public final boolean rejects(V value) {
		return value == null || wrappedRule.rejects(value) || rejectsValue(value);
	}

}
