/*
 * Created on 2004-okt-02
 */
package se.optime.repos.webdav;

import org.apache.commons.httpclient.HttpURL;

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
        RepositoryResource res = new RepositoryResource();
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
    }
    
    public void testContents() {
        RepositoryResource res = new RepositoryResource();
        assertFalse("Nothing changed yet",res.isChanged());
        res.setContents("hepp");
        assertTrue("Now simething has changed",res.isChanged());
    }
    
}
