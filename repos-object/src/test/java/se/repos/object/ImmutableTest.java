package se.repos.object;

import org.junit.Test;

import junit.framework.JUnit4TestAdapter;
import static org.junit.Assert.*;

public class ImmutableTest {

	public static junit.framework.Test suite() { 
	    return new JUnit4TestAdapter(ImmutableTest.class); 
	}
	
	@Test public void testAnnotationExists() {
		ImmutableType obj = new ImmutableType();
		assertNotNull("Should have the annotation set in runtime", 
				obj.getClass().getAnnotation(Immutable.class));
	}
	
	/**
	 * Class that uses the Immutable annotation
	 * @author solsson
	 */
	@Immutable
	class ImmutableType {
		public ImmutableType() {
			// just instantiate
		}
	}
	
}
