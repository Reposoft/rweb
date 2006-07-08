/* $license_header$
 */
package se.repos.issu;

import org.springframework.beans.BeansException;
import org.springframework.context.ApplicationContext;
import org.springframework.context.support.ClassPathXmlApplicationContext;

public class ContextForTesting {

	private static ApplicationContext testContext = null;
	
	protected ContextForTesting() throws BeansException {
	}
	
	private static void createInstance() {
		testContext = new ClassPathXmlApplicationContext("test-context.xml");
		new DatabaseSetup(testContext);
	}
	
	public static ApplicationContext getInstance() {
		if (testContext == null) {
			createInstance();
		}
		return testContext;
	}
	
	@SuppressWarnings("unchecked")
	public static <T> T getBean(String nameInContext, Class<T> typecastTo) {
		return (T) getInstance().getBean(nameInContext, typecastTo);
	}
}
