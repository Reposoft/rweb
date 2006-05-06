package se.repos.validation.annotations;

import java.lang.annotation.Documented;
import java.lang.annotation.ElementType;
import java.lang.annotation.Target;

/**
 * Denotes that a container managed bean should autamatically apply annotaded constraints on incoming method calls.
 */
@Target(ElementType.TYPE)
@Documented
public @interface ReposValidation {

}
