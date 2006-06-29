package se.repos.validation.teststrategies;

import se.repos.validation.ValidationRejectStrategy;

/**
 * Alternative way to program the constraint if there's a simple boolean expression.
 */
public class RejectNumberIsNotPositive extends ValidationRejectStrategy<Integer> {
	public boolean rejects(Integer value) { return value < 0; }
}