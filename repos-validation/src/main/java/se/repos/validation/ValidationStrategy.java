package se.repos.validation;

public interface ValidationStrategy<V> {

	ValidationResult ACCEPT = ValidationResult.VALID;
	ValidationResult REJECT = ValidationResult.INVALID;
	
	public ValidationResult validate(V value);
}
