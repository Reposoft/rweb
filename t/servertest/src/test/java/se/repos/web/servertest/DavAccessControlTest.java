package se.repos.web.servertest;

import static org.junit.Assert.assertEquals;

import java.io.IOException;

import org.junit.Test;

import se.repos.restclient.ResponseHeaders;
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
	public void testAreaUser() throws IOException {
		RestClient c = getClient(null);
		assertEquals("Should be prompted for authentication in user area",
				401, c.head("/dav/user/").getStatus());
		assertEquals("Should be prompted for authentication in user areas, before redirect so usernames can not be probed",
				401, c.head("/dav/user/test").getStatus());
		assertEquals("Should be prompted for authentication in user areas",
				401, c.head("/dav/user/test/").getStatus());
		
		RestClient ctest = getClient("test", "test");
		assertEquals("Test server up", 200, ctest.head("/").getStatus());
		assertEquals("Should be able to access the personal folder", 
				200, ctest.head("/dav/user/test/").getStatus());
// not a requirement
//		ResponseHeaders redir1 = ctest.head("/dav/user/test");
//		assertEquals("Should be redirected when trailing slash is missing",
//				301, redir1.getStatus());
//		assertEquals("Should be redirect to user folder",
//				getServer() + "/dav/user/test/", redir1.get("Location"));
		assertEquals("Should be forbidden to access the other users' folders",
				403, ctest.head("/dav/user/other/").getStatus());
		assertEquals("Test username with matching substring",
				403, ctest.head("/dav/user/test2/").getStatus());
		assertEquals("Test username with matching suffix",
				403, ctest.head("/dav/user/est/").getStatus());
		
		RestClient ctest2 = getClient("test2", "test2");
		assertEquals("Test different user",	200, ctest2.head("/dav/user/test2/").getStatus());
		assertEquals("Should be forbidden", 403, ctest2.head("/dav/user/test/").getStatus());
		
	}
	
	@Test
	public void testAreaWork() {
		// Work area must use either private url concept or user specific folder
		// Private URLs solves directory listing and works well with sharing a work unit
		// User specific work folders are easier to manage without any kind of database
		// User specific folders also map to locks in svn because they are also user specific
		
	}
	
	@Test
	public void testAreaPublic() {
		// Should we even have a public area, that lists all content to anyone?
		// Share area might be sufficient?
		// A use case might be transfer of files from public to users, 
		// but in that case we need public writable paths that are only listable to users
		// as well as drag-and-drop from public to user
		// A better implementation of this would be a separate form with upload
		// (and/or HTML5 drag-and-drop) that puts the files somewere in the dav area.
	}
	
	@Test
	public void testAreaShare() {
		// Share area should contain generated folder names.
		// Share should not be listable at the top level,
		// but possible to browse for anyone at the private URL level
		// (only those who know the URL will find the folder anyway)
		// Authenticated users should be allowed to edit or delete shares
		// We might not want to differ between edit and delete on the webdav level yet, due to rule complexity
	}
	
	@Test
	public void testAreaAllUsers() {
		// Don't know what to call this folder yet.
		// It should be a general drop-anything-here folder,
		// readwritable to any authenticated user.
		// Will probably be a mess.
		
	}
	
	public void testAreaUpload() {
		// Requires authentication
		// Initially a simple setup where all users can access everything
		
	}
	
}
