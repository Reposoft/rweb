package se.repos.validation.teststrategies;

import se.repos.validation.ValidationResult;
import se.repos.validation.ValidationStrategy;

/**
 * Constraint that accepts values that are non-negative.
 */
public class NumberShouldBePositive implements ValidationStrategy<Integer> {
	public ValidationResult validate(Integer value) {
		return value >= 0 ? ACCEPT : REJECT;
	}
}
