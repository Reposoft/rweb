/*
 * Created on 2004-okt-02
 */
package se.optime.repos.webdav;

import java.io.IOException;
import java.net.URL;

import org.apache.commons.httpclient.HttpURL;
import org.apache.commons.httpclient.URIException;
import org.apache.webdav.lib.WebdavResource;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.WebResource;
import se.optime.repos.user.MockAuthenticationResolver;

import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryResourceTest extends TestCase {

    MockAuthenticationResolver auth = null;

    public void setUp() throws Exception {
        auth = new MockAuthenticationResolver();
    }
    
    public void testAutmaticUserHandling() throws Exception {
        WebResource res = new RepositoryResource();
        auth.assertAskedFor();
    }
    
    public void testGetHttpURL() throws Exception {
        RepositoryResource res = new RepositoryResource();
        res.setSecure(false);
        res.setHost("host.test");
        res.setRepo("/repo");
        res.setPath("/path/");
        res.setHref("file.f");
        // check resulting path
        assertEquals("Getter","host.test",res.getHost());
        assertEquals("Getter","/repo",res.getRepo());
        assertEquals("Getter","/path/",res.getPath());
        assertEquals("Getter","file.f",res.getFilename());
        assertEquals("Resulting path","/repo/path/file.f",res.getAbsolutePath());
        // check httpclient url
        HttpURL url = null;
        try {
            url = res.getHttpURL();
        } catch (RuntimeException e) {
            fail("Url construction caused exception: " + e.getMessage());
        }
        assertNotNull("url object should be contructed",url);
        assertEquals("Complete url","http://host.test:80/repo/path/file.f",url.toString());
        auth.assertAskedFor();
        assertEquals("Username",MockAuthenticationResolver.TESTUSER,url.getUser());
        assertEquals("Password",MockAuthenticationResolver.TESTPASS,url.getPassword());
        // get secure url
        res.setSecure(true);
        try {
            url = res.getHttpURL();
        } catch (RuntimeException e) {
            fail("Secure url construction caused exception: " + e.getMessage());
        }
        assertTrue("https url",url.toString().startsWith("https://"));
    }
    
    /**
     * Tries to establish a HTTP connection = not isolated enough as a unit test
     * Remove if it causes any problems
     * @throws Exception
     */
    public void testGetDavFileInvalidDomain() throws Exception {
        final int EXPECTED_ERROR_CODE = 2;
        RepositoryResource res = 
            new RepositoryResource() {
            	protected HttpURL getHttpURL() {
            	    try {
                        return new HttpURL("http://error.notadomain/hello/");
                    } catch (URIException e) {
                        fail("[test-error] could not create test url");
                        return null;
                    }
            	}
        	};
        try {
            WebdavResource file = res.getDavFile();
            fail("Should have thrown an exception on invalid url");
        } catch (ConnectionException e) {
            IOException ex = (IOException) e.getCause();
        }
    }
    
    public void testGetDavFileHttpException() throws Exception {
        // une dynamock for Webdav interface
    }
    
    public void testGetUrl() throws Exception {
        RepositoryResource res = new RepositoryResource();
        res.setHost("host.test");
        res.setRepo("/repo");
        res.setPath("/path/");
        res.setHref("file.f");
        res.setSecure(true);
        try {
	        URL url = res.getURL();
	        assertEquals("java url","https://host.test:443/repo/path/file.f",url.toString());
        } catch (Exception e) {
            fail("Making URL caused exception");
        }
    }
    
    public void testGetUrlInvalid() throws Exception {
        RepositoryResource res = new RepositoryResource();
        res.setHost("test.toast");
        res.setRepo(null);
        res.setPath("/hupp/");
        res.setHref("file.f");
        try {
	        URL url = res.getURL();
	        fail("Invalid URL should cause runtime exception. Returned " + url.toString());
        } catch (Exception e) {
            RepositoryAccessException ex = (RepositoryAccessException)e;
            assertEquals("Invalid url components",RepositoryAccessException.RESULTING_URL_INVALID,ex.getError());
        }
    }   
    
    public void testGetIdentifierQuery() throws Exception {
        RepositoryResource res = new RepositoryResource();
        res.setHost("host.test");
        res.setRepo("/repo");
        res.setPath("/path/");
        res.setHref("file.f");
        res.setSecure(true);
        String q = res.getIdentifierQuery();
        assertTrue("Host should be specified",q.indexOf("host.test")>0);
        assertTrue("Repo should be specified",q.indexOf("/repo")>0);
        assertTrue("Path should be specified",q.indexOf("/path")>0);
        assertTrue("Href should be specified",q.indexOf("file.f")>0);
    }
    
    public void testPort() throws Exception {
        RepositoryResource res = new RepositoryResource();
        res.setSecure(false);
        assertEquals("default port",80,res.getPort());
        res.setPort(1080);
        assertEquals("custom",1080,res.getPort());
    }
}
