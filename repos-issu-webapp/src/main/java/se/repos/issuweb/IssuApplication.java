/* $license_header$
 */
package se.repos.issuweb;

import org.springframework.context.ApplicationContext;

import se.repos.issu.persistence.DatabaseSetup;
import se.repos.issuweb.start.Start;
import wicket.spring.SpringWebApplication;
import wicket.spring.injection.annot.SpringComponentInjector;

public class IssuApplication extends SpringWebApplication {

	@Override
	protected void init() {
		addComponentInstantiationListener(new SpringComponentInjector(this));
		setUpDatabase();
	}

	@Override
	public Class getHomePage() {
		return Start.class;
	}

	private void setUpDatabase() {
		ApplicationContext applicationContext = this.getSpringContextLocator().getSpringContext();
		new DatabaseSetup(applicationContext);
	}

}
