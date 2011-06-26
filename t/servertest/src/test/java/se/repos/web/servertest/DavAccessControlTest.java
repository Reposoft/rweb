package se.repos.web.servertest;

import static org.junit.Assert.assertEquals;

import java.io.IOException;

import org.junit.Test;

import se.repos.restclient.RestAuthentication;
import se.repos.restclient.RestClient;
import se.repos.restclient.auth.RestAuthenticationSimple;
import se.repos.restclient.hc.RestClientHc;

public class DavAccessControlTest {

	private String getServer() {
		return Fixture.Server.Multirepo.getRoot();
	}
	
	private RestClient getClient(final String username, final String password) {
		RestAuthentication auth = new RestAuthenticationSimple(username, password);
		return getClient(auth);
	}

	private RestClient getClient(RestAuthentication auth) {
		return new RestClientHc(getServer(), auth);
	}
	
	@Test
	public void test() throws IOException {
		RestClient c = getClient(null);
		assertEquals("Should be prompted for authentication in user area",
				401, c.head("/dav/user/").getStatus());
		
		RestClient ctest = getClient("test", "test");
		assertEquals("Test server up", 200, ctest.head("/").getStatus());
		assertEquals("Should be able to access the personal folder", 
				200, ctest.head("/dav/user/test/").getStatus());
		assertEquals("Should be forbidden to access the other users' folders",
				403, ctest.head("/dav/user/other/").getStatus());
		assertEquals("Test username with matching substring",
				403, ctest.head("/dav/user/test2/").getStatus());
		
		RestClient ctest2 = getClient("test2", "test2");
		assertEquals("Test different user",	200, ctest2.head("/dav/user/test2/").getStatus());
		assertEquals("Should be forbidden", 403, ctest2.head("/dav/user/test/").getStatus());
		
	}

}
