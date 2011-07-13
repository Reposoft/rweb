package se.repos.web.servertest;

import static org.junit.Assert.*;

import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;

import org.junit.Test;

import com.googlecode.sardine.DavResource;
import com.googlecode.sardine.Sardine;
import com.googlecode.sardine.SardineFactory;
import com.googlecode.sardine.util.SardineException;

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
	public void destDavRoot() {
		// Nice if it is browsable but on the other hand a lot of subfolders should probably be hidden
		// No write access needed assuming that structure is configured by server admin using filesystem
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
		assertEquals("Should be able to access the personal folder", 
				200, ctest.head("/dav/user/test/").getStatus());
		ResponseHeaders redir1 = ctest.head("/dav/user/test");
		assertEquals("Should be redirected when trailing slash is missing",
				301, redir1.getStatus());
		assertEquals("Should be redirect to user folder",
				getServer() + "/dav/user/test/", redir1.get("Location").get(0));
		assertEquals("Should be forbidden to access the other users' folders",
				403, ctest.head("/dav/user/other/").getStatus());
		assertEquals("Test username with matching substring",
				403, ctest.head("/dav/user/test2/").getStatus());
		assertEquals("Test username with matching suffix",
				403, ctest.head("/dav/user/est/").getStatus());
		
		RestClient ctest2 = getClient("test2", "test2");
		assertEquals("Test different user",	200, ctest2.head("/dav/user/test2/").getStatus());
		assertEquals("Should be forbidden", 403, ctest2.head("/dav/user/test/").getStatus());

		// How about the /dav/user/ folder?
		// If users want to connecto to the full /dav/ share they need read access to /dav/user/ so they can browse to their server.
		// The ideal would be like in the browser view of the dav area, that forbidden folders are hidden
		
	}
	
	@Test
	public void testAreaWork() {
		// Work area must use either private url concept or user specific folders
		// Private URLs solves directory listing and works well with sharing a work unit
		// User specific work folders are easier to manage without any kind of database
		// User specific folders also map to locks in svn because they are also user specific
		
	}
	
	@Test
	public void testAreaPublic() throws IOException {
		// Should we even have a public area, that lists all content to anyone?
		// Share area might be sufficient?
		// A use case might be transfer of files from public to users, 
		// but in that case we need public writable paths that are only listable to users
		// as well as drag-and-drop from public to user
		// A better implementation of this would be a separate form with upload
		// (and/or HTML5 drag-and-drop) that puts the files somewere in the dav area.
		// See also 'upload' area concept.
		
		RestClient c = getClient(null);
		int publicStatus = c.head("/dav/public/").getStatus();
		//assertEquals(200, publicStatus);
		assertTrue(publicStatus == 200 || publicStatus == 404);
		if (publicStatus == 200) {
			Sardine davtest = SardineFactory.begin();
			try {
				davtest.getResources(getServer() + "/dav/public/");
			} catch (Exception e) {
				e.printStackTrace();
				fail("Public area is optional on servers, but if the folder exists it should be readable using WebDAV");
			}
		}
	}
	
	@Test
	public void testAreaUpload() {
		// Requires authentication
		// Should be writable by anyone but readable only to authenticated users
		// Webdav might not be the best solution for this use case
		
	}
	
	@Test
	public void testAreaShare() throws IOException {
		// Share area should contain generated folder names.
		// Share should not be listable at the top level,
		// but possible to browse for anyone at the private URL level
		// (only those who know the URL will find the folder anyway so we don't need to care about ownership)
		// Authenticated users should be allowed to edit or delete shares
		// We might not want to differ between edit and delete on the webdav level yet, due to rule complexity

		String existingShare = "/dav/share/20200101-m8yAL7Dr6C/";
		String newShare = "/dav/share/" + new SimpleDateFormat("yyyyMMdd").format(new Date()) + "-" + 
					Long.toString(System.currentTimeMillis()).substring(3, 13);
		newShare = newShare + "/"; // dav client gets redirect for delete without trailing slash
		
		RestClient c = getClient(null);
		assertEquals("No one should be allowed to list shares",
				403, c.head("/dav/share/").getStatus());
		assertEquals(403, c.head("/dav/share/index.html").getStatus());
		assertEquals(403, c.head("/dav/share/index.php").getStatus());
		assertEquals("Should get a forbidden when share does not exist, not a 401 (or should we?)",
				403, c.head("/dav/share/20200101-xxxxxxxxxx/").getStatus());
		assertEquals("Existing shares should be public readable",
				200, c.head(existingShare).getStatus());
		ResponseHeaders missingSlash = c.head("/dav/share/20200101-m8yAL7Dr6C");
		assertEquals("Should be redirected when trailing slash is missing",
				301, missingSlash.getStatus());
		assertEquals(getServer() + existingShare, missingSlash.get("Location").get(0));
		
		RestClient ctest = getClient("test", "test");
		assertEquals("Not even authenticated users should be allowed to list shares",
				403, ctest.head("/dav/share/").getStatus());
		
		String testid = this.getClass().getName() + System.currentTimeMillis();
		assertEquals("Test file should not exist yet",
				404, c.head(existingShare + testid).getStatus());
		Sardine d = SardineFactory.begin();
		Sardine dtest = SardineFactory.begin("test", "test");
		dtest.put(getServer() + existingShare + testid, "testfilecontents".getBytes());
		assertEquals("Authenticated user should be allowed to add contents to share that public can read",
				200, c.head(existingShare + testid).getStatus());
		try {
			d.delete(getServer() + existingShare + testid);
			fail("Only authenticated users should be allowed to edit/delete share contents");
		} catch (SardineException e) {
			assertTrue("Got: " + e, e.getMessage().contains("Authorization Required"));
		}
		dtest.delete(getServer() + existingShare + testid);
		assertEquals("Authenticated user should be allowed to delete the file",
				404, c.head(existingShare + testid).getStatus());
		
		try {
			dtest.createDirectory(getServer() + newShare);
		} catch (SardineException e) {
			e.printStackTrace();
			fail("Authenticated user should be allowed to create new share");
		}
		assertEquals("Created share should be publicly readable", 
				200, c.head(newShare).getStatus());
		try {
			dtest.delete(getServer() + newShare);
		} catch (SardineException e) {
			e.printStackTrace();
			fail("Authenticated user should be allowed to delete share");
		}
		
		try {
			dtest.createDirectory(getServer() + "/dav/share/somedir" + System.currentTimeMillis());
			fail("Should only be allowed to create folders that match the share name rule");
		} catch (SardineException e) {
			// expected
		}
	}
	
	@Test
	public void testAreaAllUsers() throws IOException {
		// Don't know what to call this folder yet.
		// It should be a general drop-anything-here folder,
		// readwritable to any authenticated user.
		// Will probably be very unstructured -- the 'share' concept is preferred
		// because shares have a limited life time and scope
		
		RestClient c = getClient(null);
		assertEquals("Users area should always require login",
				401, c.head("/dav/allusers/").getStatus());
		
		RestClient ctest = getClient("test", "test");
		assertEquals(200, ctest.head("/dav/allusers/").getStatus());
		
		RestClient ctest2 = getClient("test2", "test2");
		assertEquals(200, ctest2.head("/dav/allusers/").getStatus());
	}
	
}
