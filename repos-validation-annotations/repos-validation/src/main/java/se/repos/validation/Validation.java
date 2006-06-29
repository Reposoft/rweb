package se.repos.validation;

import java.lang.reflect.InvocationTargetException;

import se.repos.validation.impl.Rule;
import se.repos.validation.impl.RuleSet;
import se.repos.validation.objects.RejectIsInnerClass;
import se.repos.validation.objects.ShouldHaveDefaultConstructor;

/**
 * Factory to create validation rules on one line given a validation strategy.
 */
public abstract class Validation {

	/**
	 * The validation rule that enforces the prerequisites of a strategy that can be instantiated here.
	 * @todo do as annotations
	 */
	private static final ValidationRule<Class> STRATEGY_CLASS_RULE = 
		new RuleSet<Class>(
				rule(new RejectIsInnerClass()),
				rule(new ShouldHaveDefaultConstructor()));
	
	/**
	 * Creates an immutable object that can validate a parameter
	 * @param validationStrategy The logic for the constraint
	 * @return A validator instance with the given logic/constraint/rule
	 */
	public static <V> ValidationRule<V> rule(ValidationStrategy<V> validationStrategy) {
		return new Rule<V>(validationStrategy);
	}

	/**
	 * Creates a validation rule using a strategy class.
	 * @param validationStrategyClass Strategy implementation that has a default constructor and is a normal (not inner) class
	 * @return A validator using an instance of the strategy class
	 */
	public static <V> ValidationRule<V> rule(Class<? extends ValidationStrategy<V>> validationStrategyClass) {
		STRATEGY_CLASS_RULE.validate(validationStrategyClass);
		ValidationStrategy<V> strategy = getStrategyInstance(validationStrategyClass);
		return rule(strategy);
	}
	
	/**
	 * Runs validation from annotations on the calling method.
	 * @see se.repos.validation.annotations.Constraint
	 * @see se.repos.validation.annotations.ValidationStrategyAnnotation
	 */
	public static void annotated() {
		
	}
	
	/**
	 * Helper method to create strategy instance based on class name
	 * @param <V> The class that the validation rule is made for
	 * @param validationStrategyClass The strategy class, verified that it has a no-args constructor
	 * @return a new instance of the strategy class
	 */
	private static <V> ValidationStrategy<V> getStrategyInstance(Class<? extends ValidationStrategy<V>> validationStrategyClass) {
		try {
			return validationStrategyClass.getConstructor().newInstance();
		} catch (IllegalArgumentException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SecurityException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (InstantiationException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IllegalAccessException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (InvocationTargetException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (NoSuchMethodException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		throw new RuntimeException("Could not instantiate validation strategy with the default constructor in " + validationStrategyClass);
	}
}
