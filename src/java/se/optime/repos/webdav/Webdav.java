/*
 * Created on 2004-okt-09
 */
package se.optime.repos.webdav;

import java.io.IOException;

import org.apache.commons.httpclient.HttpException;
import org.apache.commons.httpclient.HttpURL;
import org.apache.webdav.lib.WebdavResource;

/**
 * Representing instantiations of WebDAV objects.
 * @author solsson
 * @version $Id$
 */
public interface Webdav {

	/**
	 * 
	 * @param url
	 * @return Connected resource
	 * @throws HttpException
	 * @throws IOException
	 */
    public WebdavResource getWebdavResource(HttpURL url) throws HttpException, IOException;
    
}
