/* $license_header$
 */
package se.repos.svn;

import junit.framework.TestCase;

public class SvnProxySettingsTest extends TestCase {

	public void testEquals() {
		SvnProxySettings p1 = new SvnProxySettings("1.2.3.4", 88);
		p1.setUsername("user");
		p1.setPassword("password");
		SvnProxySettings p2 = new SvnProxySettings("1.2.3.4", 88);
		p2.setUsername("user");
		p2.setPassword("password");
		
		assertTrue("Same settings, should be equal", p1.equals(p2));
	}

	public void testEqualsNo() {
		SvnProxySettings p1 = new SvnProxySettings("1.2.3.4", 88);
		p1.setUsername("user");
		p1.setPassword("password");
		SvnProxySettings p2 = new SvnProxySettings("1.2.3.4", 88);
		
		assertFalse("Not same credentials, should not be equal", p1.equals(p2));
	}	
	
	public void testHashCode() {
		SvnProxySettings p1 = new SvnProxySettings("1.2.3.4", 88);
		p1.setUsername("user");
		p1.setPassword("password");
		SvnProxySettings p2 = new SvnProxySettings("1.2.3.4", 88);
		p2.setUsername("user");
		p2.setPassword("password");
		
		assertEquals(p1.hashCode(), p2.hashCode());
	}
	
	public void testNoProxy() {
		assertEquals("", SvnProxySettings.NOPROXY.getHost());
		assertEquals("", SvnProxySettings.NOPROXY.getPort());
		assertEquals(null, SvnProxySettings.NOPROXY.getUsername());
		assertEquals(null, SvnProxySettings.NOPROXY.getPassword());
		assertEquals("", SvnProxySettings.NOPROXY.toString());
	}

}
