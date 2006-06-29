package se.repos.validation.objects;

import se.repos.validation.ValidationResult;
import se.repos.validation.ValidationStrategy;
import junit.framework.TestCase;

public class RejectIsInnerClassTest extends TestCase {
	
	public void testValidateWithNormalClass() {
		assertTrue(new RejectIsInnerClass().validate(Object.class).passed());
	}
	
	public void testValidateWithAnonymousClass() {
		ValidationStrategy anonymous = new ValidationStrategy() {
			public ValidationResult validate(Object value) { return ValidationResult.VALID; }			
		};
		anonymous.getClass().isAnonymousClass();
		assertTrue("should reject anonymous class",
				new RejectIsInnerClass().validate(anonymous.getClass()).failed());
	}
	
	public void testValidateWithInternalStrategyClass() {
		assertTrue("should reject member class",
				new RejectIsInnerClass().validate(MemberClass.class).failed());
	}
	
	private class MemberClass implements ValidationStrategy<Object> {
		public ValidationResult validate(Object value) { return ValidationResult.VALID; }
	}
	
}
