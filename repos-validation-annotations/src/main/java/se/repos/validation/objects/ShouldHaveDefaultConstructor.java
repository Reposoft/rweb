package se.repos.validation.objects;

import java.lang.reflect.Constructor;

import se.repos.validation.ValidationResult;
import se.repos.validation.ValidationStrategy;

public class ShouldHaveDefaultConstructor implements
		ValidationStrategy<Class> {

	public ValidationResult validate(Class value) {
		for (Constructor c : value.getConstructors()) {
			if (c.getParameterTypes().length == 0) {
				return ACCEPT;
			}
		}
		return REJECT;
	}

}
