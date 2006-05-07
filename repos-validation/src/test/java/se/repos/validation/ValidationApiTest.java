package se.repos.validation;

import org.junit.Test;

import junit.framework.JUnit4TestAdapter;

public class ValidationApiTest {

	// TODO: setter validation and field name resolution
	
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

	@Test(expected=IllegalArgumentException.class) public void checkArgumentBeforeProceeding_Fail() {
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
	 * Constraint that accepts values that are non-negative.
	 */
	class NumberShouldBePositive implements ValidationStrategy<Integer> {
		public ValidationResult validate(Integer value) {
			return value >= 0 ? ACCEPT : REJECT;
		}
	}
	
	/**
	 * Alternative way to program the constraint if there's a simple boolean expression.
	 */
	class RejectNumberIsNotPositive extends ValidationRejectStrategy<Integer> {
		public boolean rejects(Integer value) { return value < 0; }
	}
}
