package se.repos.validation.annotations;

import java.lang.annotation.Documented;
import java.lang.annotation.ElementType;
import java.lang.annotation.Target;

import se.repos.validation.ValidationStrategy;

/**
 * 
 * To be declared for method parameters.
 * If a method has only one argument it can be set on the method.
 */
@Target({ElementType.METHOD, ElementType.PARAMETER})
@Documented
public @interface Constraint {
	Class<? extends ValidationStrategy<?>> value();
}
