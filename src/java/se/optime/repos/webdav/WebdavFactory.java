/*
 * Created on 2004-okt-09
 */
package se.optime.repos.webdav;

import java.io.IOException;

import org.apache.commons.httpclient.HttpException;
import org.apache.commons.httpclient.HttpURL;
import org.apache.webdav.lib.WebdavResource;

/**
 * A very thin abstraction layer above jakarta slide WebDAV client.
 * 
 * <p>The purpose is to allow beans without dependencies (such as mvc commands)
 * to act as WebDAV proxies, without the need to use cunstructors directly.
 * The default implementation returned by {@link #getWebdav() getWebdav} 
 * simply wraps calls to jakarta slide WebDAV library constructors.</p>
 * 
 * <p>To support unit testing, there is a {@link #setWebdav(Webdav) setWebdav} for changing the implementation
 * that is returned to all callers of the facory method. The implementation remains the same until set again.</p>
 * 
 * @author solsson
 * @version $Id$
 */
public class WebdavFactory {

    private static Webdav webdav = new JakartaSlideWebdavWrapper();
    
    protected WebdavFactory() {
        throw new UnsupportedOperationException("This is a static factory");
    }
    
    /**
     * @param webdavImplementation Webdav implementation other than the default.
     */
    static void setWebdav(Webdav webdavImplementation) {
        webdav = webdavImplementation;
    }
    
    /**
     * Default implementation made for static access.
     * @author solsson
     * @version $Id$
     */
    private static class JakartaSlideWebdavWrapper implements Webdav {

        /* (non-Javadoc)
         * @see se.optime.repos.webdav.WebdavRepository#getWebdavResource(org.apache.commons.httpclient.HttpURL)
         */
        public WebdavResource getWebdavResource(HttpURL url) throws HttpException, IOException {
            return new WebdavResource(url);
        }
        
    }
   
    /**
     * @return Returns the webdav.
     */
    public static Webdav getWebdav() {
        return webdav;
    }
}
