/* $license_header$
 */
package se.repos.issu;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;

import javax.sql.DataSource;

import org.springframework.beans.BeansException;
import org.springframework.context.ApplicationContext;
import org.springframework.context.support.ClassPathXmlApplicationContext;
import org.springframework.core.io.Resource;
import org.springframework.jdbc.object.SqlUpdate;

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
	
	public static <T> T getBean(String nameInContext, Class<T> typecastTo) {
		return (T) getInstance().getBean(nameInContext, typecastTo);
	}
}
