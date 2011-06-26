package se.repos.web.servertest;

import static org.junit.Assert.*;

import java.util.List;

import org.junit.Test;

import com.googlecode.sardine.DavResource;
import com.googlecode.sardine.ReposSardineClientCustomization;
import com.googlecode.sardine.Sardine;
import com.googlecode.sardine.SardineFactory;
import com.googlecode.sardine.util.SardineException;

public class DavWindowsCompatibilityTest {

	// Windows network folder does login, verification (to see if "URL is valid")
	// and folder operations without the trailing slash
	
	@Test
	public void testFolderWithoutTrailingSlash() throws SardineException {
		String server = Fixture.Server.Multirepo.getRoot();
		String username = "test";
		String password = "test";
		
		Sardine davClient = SardineFactory.begin(username, password);
		try {
			davClient.getResources(server + "/dav/user/test");
			fail("With a non-microsoft user agent folder operations without trailing slash should result in redirect");
		} catch (SardineException e) {
			// expected
		}
		
		// custom initialization of sardine to be able to get hold of the HttpClient
		// and set user agent
		String userAgent = "Microsoft Data Access Internet Publishing Provider DAV 1.1";
		ReposSardineClientCustomization.setUserAgent(davClient, userAgent);
		
		String userFolder = server + "/dav/user/test";
		List<DavResource> resources = davClient.getResources(userFolder);
		int sizeBefore = resources.size();
		assertTrue("Should have made a successful propfind without the trailing slash", sizeBefore > 0);
		
		String newfolder = userFolder + "/microsofttestdir" + System.currentTimeMillis();
		davClient.createDirectory(newfolder);
		List<DavResource> r2 = davClient.getResources(userFolder);
		assertEquals("Should have created a new folder", sizeBefore + 1, r2.size());
		
		davClient.delete(newfolder);
		List<DavResource> r3 = davClient.getResources(userFolder);
		assertEquals("Should have deleted the folder without using trailing slash", sizeBefore, r3.size());
		
		// The client does some more funky stuff when trying to create a network folder, but I'm not sure exactly what requests
		// At one attempt I managed to get an error that indicates that it tries to lock something.
	}
	

	
	
	
}
