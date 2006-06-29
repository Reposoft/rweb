package se.repos.validation.objects;

import se.repos.validation.ValidationRejectStrategy;

/**
 * Validates that a class is a normal class (not anonymous, local or member).
 * @author solsson
 */
public class RejectIsInnerClass extends ValidationRejectStrategy<Class> {

	@Override
	public boolean rejects(Class clazz) {
		return clazz.isAnonymousClass() || clazz.isLocalClass() || clazz.isMemberClass();
	}

}
