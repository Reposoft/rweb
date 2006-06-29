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
 * A combination of elementary validation rules to form a complex rule.
 * 
 * Must be immutable and stateless, which means that it does not store fieldName,
 * and it expects the validation rules to be stateless too.
 * 
 * @author Staffan Olsson
 * @since 2005-nov-15
 * @version $Id$
 */
public final class RuleSet<V> extends ValidationRuleBase<V> {

    ValidationRule<V>[] rules;

    public RuleSet(ValidationRule<V>... rules) {
        this.rules = rules;
    }

    /* (non-Javadoc)
     * @see se.repos.tools.validation.ValidationRuleBase#validate(V)
     */
    @Override
    public void validate(V value) throws IllegalArgumentException {
        for (ValidationRule<V> r : rules) {
            r.validate(value);
        }
    }

    /* (non-Javadoc)
     * @see se.repos.tools.validation.ValidationRuleBase#validate(V, java.lang.String)
     */
    @Override
    public void validate(V value, String fieldName) throws IllegalArgumentException {
        throw new UnsupportedOperationException("very deprecated");
    }

    /* (non-Javadoc)
     * @see se.repos.tools.validation.ValidationRuleBase#rejectsValue(V)
     */
    @Override
    public boolean rejectsValue(V value) {
        boolean reject = false;
        for (int i = 0; !reject && i < rules.length; i++) {
            reject |= rules[i].rejects(value);
        }
        return reject;
    }

}
