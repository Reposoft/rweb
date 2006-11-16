/* $license_header$
 */
package se.repos.svn.checkout.managed;

import org.easymock.MockControl;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.config.ClientConfiguration;
import junit.framework.TestCase;

public class DefaultReposClientSettingsTest extends TestCase {

	public void testUpdateTEMP() {
		MockControl configControl = MockControl.createNiceControl(ClientConfiguration.class);
		ClientConfiguration config = (ClientConfiguration) configControl.getMock();
		
		config.getGlobalIgnores();
		configControl.setReturnValue(new SvnIgnorePattern[]{}, 
				9); // how many patterns are there?
		config.addGlobalIgnore(new SvnIgnorePattern("TEMP"));
		config.addGlobalIgnore(new SvnIgnorePattern("Temp"));
		config.addGlobalIgnore(new SvnIgnorePattern("temp"));
		configControl.replay();
		
		DefaultReposClientSettings settings = new DefaultReposClientSettings();
		settings.update(config);
		
		configControl.verify();
	}

}
