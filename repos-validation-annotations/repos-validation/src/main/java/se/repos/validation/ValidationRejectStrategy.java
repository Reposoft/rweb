package se.repos.validation;

/**
 * Superclass for validation strategies with a simple one line boolean expression.
 * @param <V> The type of value that can be validated
 */
public abstract class ValidationRejectStrategy<V> implements ValidationStrategy<V> {

	public abstract boolean rejects(V value);

	public final ValidationResult validate(V value) {
		return rejects(value) ? REJECT : ACCEPT;
	}
}
