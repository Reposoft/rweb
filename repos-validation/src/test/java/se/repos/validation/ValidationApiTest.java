package se.repos.validation;

import se.repos.validation.annotations.Constraint;
import junit.framework.TestCase;

public class ValidationApiTest extends TestCase {

	final ValidationRule<Integer> theRule = Validation.rule(new RejectNumberIsNotOne());
	
	public void testOneLineValidation() {
		try {
			theRule.validate(1);
		} catch (IllegalArgumentException e) {
			fail("Argument should be valid, but got exception: " + e);
		}
	}

	public void testOneLineValidationFail() {
		try {
			// validation error
			theRule.validate(2);
			fail("Should have thrown a runtime exception for the invalid value");
		} catch (IllegalArgumentException e) {
			// default error message should be decoded from strategy class name
			String defaultMessage = e.getMessage();
			assertEquals("Number is not one", defaultMessage);
		}
	}	

	public void testValidateNull() {
		try {
			// null should always be a validation error, except if ShouldBeNull is used as a rule
			theRule.validate(null);
			fail("Should have thrown a runtime exception for null value");
		} catch (IllegalArgumentException e) {
			// default error message should be decoded from strategy class name
			String defaultMessage = e.getMessage();
			assertEquals("Number is not one", defaultMessage);
		}
	}
	
	public void testCreateValidatorAsFirstLineOfMethod() {
		Validation.rule(RejectNumberIsNotOne.class);
	}
	
	public void testValidateUsingMethodAnnotation() {
		try {
			// validation error
			annotatedMethodWithOneArgument(2);
			fail("Should have thrown a runtime exception for the invalid value");
		} catch (IllegalArgumentException e) {
			// default error message should be decoded from strategy class name
			String defaultMessage = e.getMessage();
			assertEquals("Number is not one", defaultMessage);
		}
	}
	
	@Constraint(RejectNumberIsNotOne.class)
	private void annotatedMethodWithOneArgument(Integer field) {
		// call validation factory without arguments
		Validation.annotated();
		// proceed normal operations with the validated input
		
	}

	public void testValidateUsingParameterAnnotation() {
		try {
			// validation error
			annotatedMethodWithTwoArguments(1, 2);
			fail("Should have thrown a runtime exception for the second argument");
		} catch (IllegalArgumentException e) {
			// default error message should be decoded from strategy class name
			String defaultMessage = e.getMessage();
			assertEquals("Number is not one", defaultMessage);
		}
	}
	
	private void annotatedMethodWithTwoArguments(
			@Constraint(RejectNumberIsNotOne.class) Integer field1,
			@Constraint(RejectNumberIsNotOne.class) Integer field2) {
		// call validation factory without arguments
		Validation.annotated();
		// proceed normal operations with the validated input
		
	}	
	
	class RejectNumberIsNotOne implements ValidationStrategy<Integer> {
		public ValidationResult validate(Integer value) {
			return value.equals(1) ? ACCEPT : REJECT;
		}
	}
}
