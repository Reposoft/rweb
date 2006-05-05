package se.repos.validation;

/**
 * Factory to create validation rules on one line given a validation strategy.
 */
public abstract class Validation {

	/**
	 * Creates an immutable object that can validate a parameter
	 * @param validationStrategy The logic for the constraint
	 * @return A validator instance with the given logic/constraint/rule
	 */
	public static <V> ValidationRule<V> rule(ValidationStrategy<V> validationStrategy) {
		return null;
	}

	/**
	 * Creates a validation rule using a strategy class
	 * @param validationStrategy Strategy implementaiton that has a default constructor
	 * @return A validator using an instance of the strategy class
	 */
	public static <V> ValidationRule<V> rule(Class<? extends ValidationStrategy<V>> validationStrategy) {
		return null;
	}
	
	/**
	 * Runs validation from annotations on the calling method.
	 * @see se.repos.validation.annotations.Constraint
	 * @see se.repos.validation.annotations.ValidationStrategyAnnotation
	 */
	public static void annotated() {
		
	}
	
}
