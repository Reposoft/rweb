package se.repos.validation;

import org.junit.Test;

import se.repos.validation.annotations.Constraint;
import se.repos.validation.annotations.ReposValidation;
import junit.framework.JUnit4TestAdapter;

public class ValidationApiTest {

	public static junit.framework.Test suite() { 
	    return new JUnit4TestAdapter(ValidationApiTest.class); 
	}
	
	// this is how validation rules can be kept in a superclass, or created in the line that 
	final ValidationRule<Integer> RULE = Validation.rule(new NumberShouldBePositive());
	
	// trows a runtime exception if validation fails
	@Test public void checkArgumentBeforeProceeding() {
			RULE.validate(1);
			// proceed with logic
	}

	@Test(expected=IllegalArgumentException.class) public void checkArgumentBeforeProceedingFail() {
			RULE.validate(-1);
			// logic not reached
	}
	
	@Test(expected=IllegalArgumentException.class) public void checkThatValidationOfNullAlwaysFails() {
		RULE.validate(null);
		// no one can do calculatinons with null value
		// _if_ null means something, this method can probably not benefit from one line constraints
	}
	
	@Test public void oneLineConstraintWhenYouNeedPrerequisitesForLogic() {
		Validation.rule(NumberShouldBePositive.class).validate(1);
		// or
		Validation.rule(RejectNumberIsNotPositive.class).validate(1);
		// or
		Validation.rule(new RejectNumberIsNotPositive()).validate(1);
	}
	
	/**
	 * Does validation based on annotations before proceeding.
	 * This means that the parameter requirements are self documented.
	 * It requires {@link Validation#annotated()} to be called as the first
	 * line of the method, unless the class is marked as autovalidate and has a validationproxy. 
	 * @param field The input
	 */
	@Constraint(NumberShouldBePositive.class)
	private void annotatedMethodWithOneArgument(Integer field) {
		Validation.annotated();
		// proceed normal operations with the validated input
	}
	
	// calls a method that has annotated validation.
	@Test(expected=IllegalArgumentException.class) public void testValidateUsingMethodAnnotation() {
		annotatedMethodWithOneArgument(-1);
	}

	/**
	 * Does validation based on parameter annotations.
	 * Same functionality as above, even more self documenting,
	 * allows more than one argument, but is harder to read.
	 * @param field1 The input
	 * @param field2 The input
	 */
	private void annotatedMethodWithTwoArguments(
			@Constraint(NumberShouldBePositive.class) Integer field1,
			@Constraint(NumberShouldBePositive.class) Integer field2) {
		Validation.annotated();
		// proceed normal operations with the validated input
	}
	
	// calls a method that has annotated validation
	@Test(expected=IllegalArgumentException.class) public void testValidateUsingParameterAnnotation() {
		annotatedMethodWithTwoArguments(1, 2);
	}	
	
	/**
	 * Constraint that accepts values that are non-negative
	 */
	class NumberShouldBePositive implements ValidationStrategy<Integer> {
		public ValidationResult validate(Integer value) {
			return value >= 0 ? ACCEPT : REJECT;
		}
	}
	
	/**
	 * Alternative way to program the constraint
	 */
	class RejectNumberIsNotPositive extends ValidationRejectStrategy<Integer> {
		public boolean rejects(Integer value) { return value >= 0; }
	}
	
	
	@Test(expected=IllegalArgumentException.class) public void containerManagedValidationForAnnotations() {
		AutoValidate av = getAutoValidateInstanceFromContainer();
		av.interfaceMethod(2);
	}
	
	private AutoValidate getAutoValidateInstanceFromContainer() {
		// TODO: emulate container
		return new AutoValidate();
	}
	
	/**
	 * Class expecting AOP-style validation
	 */
	@ReposValidation
	class AutoValidate {
		@Constraint(NumberShouldBePositive.class)
		public void interfaceMethod(int argument) {
			// no need to call Validation.annotaded()
		}
	}
}
