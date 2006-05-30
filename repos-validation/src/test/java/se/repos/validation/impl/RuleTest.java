package se.repos.validation.impl;

import org.junit.Test;

import se.repos.validation.ValidationResult;
import se.repos.validation.ValidationRule;
import se.repos.validation.ValidationStrategy;

import junit.framework.JUnit4TestAdapter;
import static org.easymock.EasyMock.*;
import static org.junit.Assert.*;

public class RuleTest {

	public static junit.framework.Test suite() { 
	    return new JUnit4TestAdapter(RuleTest.class); 
	}	
	
	@Test public void testValid() {
		Object o = new Object();
		ValidationStrategy<Object> strategyMock = createMock(ValidationStrategy.class);
		// strategy should be stateless and always return the same result for the same input
		expect(strategyMock.validate(o)).andReturn(ValidationResult.VALID).times(2);
		replay(strategyMock);
		// do the test
		ValidationRule<Object> validationRule = new Rule<Object>(strategyMock);
		assertTrue(validationRule.accepts(o));
		assertFalse(validationRule.rejects(o));
		verify(strategyMock);
	}

	@Test public void testInvalid() {
		Object o = new Object();
		ValidationStrategy<Object> strategyMock = createMock(ValidationStrategy.class);
		// strategy should be stateless and always return the same result for the same input
		expect(strategyMock.validate(o)).andReturn(ValidationResult.INVALID).times(2);
		replay(strategyMock);
		// do the test
		ValidationRule<Object> validationRule = new Rule<Object>(strategyMock);
		assertFalse(validationRule.accepts(o));
		assertTrue(validationRule.rejects(o));
		verify(strategyMock);	
	}	
	
	@Test(expected=ValidationFailedException.class)
	public void testValidateThrowsOurExceptionClass() {
		Object o = new Object();
		ValidationStrategy<Object> strategyMock = createMock(ValidationStrategy.class);
		// strategy should be stateless and always return the same result for the same input
		expect(strategyMock.validate(o)).andReturn(ValidationResult.INVALID);
		replay(strategyMock);
		// check that exception is thrown
		ValidationRule<Object> validationRule = new Rule<Object>(strategyMock);
		validationRule.validate(o);
	}
	
	@Test(expected=ValidationNullValueException.class)
	public void testValidateThrowsSpecialExceptionOnNullValue() {
		ValidationStrategy<Object> strategyMock = createMock(ValidationStrategy.class);
		replay(strategyMock);
		// the strategy should never be bothered with null values
		ValidationRule<Object> validationRule = new Rule<Object>(strategyMock);
		validationRule.validate(null);
	}	

}
