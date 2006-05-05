package se.repos.validation;

import junit.framework.TestCase;

public class ValidationResultTest extends TestCase {

	public void testAll() {
		assertTrue(ValidationResult.VALID.passed());
		assertFalse(ValidationResult.VALID.failed());
		assertTrue(ValidationResult.INVALID.failed());
		assertFalse(ValidationResult.INVALID.passed());
	}
}
