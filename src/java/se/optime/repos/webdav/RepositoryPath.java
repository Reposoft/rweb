/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav;

import org.apache.commons.httpclient.HttpURL;

/**
 * @author solsson
 * @version $Id$
 */
public interface RepositoryPath {
    
    /**
     * @return hostname/address for repository
     */
    public String getHost();
    
    /**
     * @return port at the host
     */
    public int getPort();
    
    /**
     * @return repository root path, starting slash marking host root, without tailing slash 
     */
    public String getRepo();
    
    /**
     * @return in-repository path, starting slash marking repository root. Add tailing slash if specifying file.
     */
    public String getPath();
    
    /**
     * @return resource href from path ( = filename)
     */
    public String getHref();
    
    /**
     * @return resulting url
     */
    public HttpURL getHttpURL();
    
    /**
     * @return true if connection is secure
     */
    public boolean isSecure();
}
