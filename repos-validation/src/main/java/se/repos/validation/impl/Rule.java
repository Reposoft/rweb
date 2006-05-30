package se.repos.validation.impl;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import se.repos.validation.ValidationResult;
import se.repos.validation.ValidationRule;
import se.repos.validation.ValidationStrategy;

/**
 * Runnable validation based on a strategy that contains the rule/logic.
 * @author solsson
 * @param <V> The class that this rule can validate
 */
public class Rule<V> implements ValidationRule<V> {

	final Logger logger = LoggerFactory.getLogger(Rule.class);
	
	ValidationStrategy<V> strategy;
	
	public Rule(ValidationStrategy<V> validationStrategy) {
		this.strategy = validationStrategy;
	}
	
	public void validate(V value) throws IllegalArgumentException {
    	if (value == null) {
    		logger.info("Validation failed before running strategy {} because value is null", strategy);
    		throw new ValidationNullValueException();
    	}
    	if (rejects(value)) {
    		logger.info("Validation failed for value '{}' with strategy {}", value, strategy);
    		throw new ValidationFailedException(strategy, value);
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
