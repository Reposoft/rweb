package se.repos.validation.objects;

import se.repos.validation.ValidationResult;
import se.repos.validation.ValidationStrategy;
import junit.framework.TestCase;

public class ShouldHaveDefaultConstructorTest extends TestCase {

	public void testValidateOk() {
		assertTrue("Should accept class that has no-arg constructor and other consturctors",
				new ShouldHaveDefaultConstructor()
				.validate(Exception.class).passed());
	}

	public void testValidateDefault() {
		assertTrue("Should accept class that has no constructor defined",
				new ShouldHaveDefaultConstructor()
				.validate(Object.class).passed());
	}	
	
	public void testValidateNoDefault() {
		assertFalse("Should not accept class that requires argments for all constructors",
				new ShouldHaveDefaultConstructor()
				.validate(Integer.class).passed());
	}

}
