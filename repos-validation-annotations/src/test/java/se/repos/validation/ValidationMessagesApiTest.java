package se.repos.validation;

import junit.framework.TestCase;

public class ValidationMessagesApiTest extends TestCase {

	// TODO test message code and arguments
	// TODO test setter validation and the resolution of field name
	
	public void testMessageForValidationOfMethodWithOneArgument() {
		
	}
	
	private void doValidation(int value) {
		Validation.rule(DoFail.class).validate(value);
		// todo .param("value")
	}
	
	public void testMessageForValidationOfAnnotatedMethod() {
		
	}
	
	public void testMessageForValidationOfAnnotatedParameter() {
		
	}
	
	// use the classes below
	public void testMessageFromStrategyNames() {
		
	}
	
	// validation rules with no argument
	class ShouldBePositiveNumber extends DoFail {}
	class RejectNotAPositiveNumber extends DoFail {}
	class ShouldNotEndWithSlash extends DoFail {}
	class RejectStringDoesNotEndWithSlash extends DoFail {}
	
	// validation rules with arguments
	class RejectStringExceedsMaxLength extends DoFail {
		RejectStringExceedsMaxLength(int maxNumberOfCharacters) {
		}
	}
	class ShouldStartWith extends DoFail {
		ShouldStartWith(String substring) {
		}
	}
	
	/**
	 * Validation strategy that always fails
	 */
	class DoFail implements ValidationStrategy<Object> {
		public ValidationResult validate(Object value) { return REJECT; }
	}
	
	/**
	 * Returns the body of the message that would be generated for a given strategy
	 * Does not include the validated value or parameter info resolution
	 * @param validationStrategyClassName
	 * @return
	 */
	private String getDefaultMessage(String validationStrategyClassName) {
		throw new UnsupportedOperationException("Implement test helper that invokes the message resolution");
	}
	
}
