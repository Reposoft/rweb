package se.repos.validation.impl;

import se.repos.validation.ValidationResult;
import se.repos.validation.ValidationRule;
import se.repos.validation.ValidationStrategy;

/**
 * Runnable validation based on a strategy that contains the rule/logic.
 * @author solsson
 * @param <V> The class that this rule can validate
 */
public class ValidationRuleImpl<V> implements ValidationRule<V> {

	ValidationStrategy<V> strategy;
	
	ValidationRuleImpl(ValidationStrategy<V> validationStrategy) {
		this.strategy = validationStrategy;
	}
	
	public void validate(V value) throws IllegalArgumentException {
    	if (value == null) {
    		throw new ValidationNullValueException();
    	}
    	if (rejects(value)) {
    		throw new ValidationFailedException(this);
    	}
	}

	public void validate(V value, String fieldName) throws IllegalArgumentException {
		throw new UnsupportedOperationException("deprecated");
	}

	public boolean accepts(V value) {
		return ValidationResult.VALID.equals(strategy.validate(value));
	}

	public boolean rejects(V value) {
		return ValidationResult.INVALID.equals(strategy.validate(value));
	}

}
