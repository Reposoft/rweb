/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav;

import org.apache.commons.httpclient.HttpURL;
import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.View;

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
    
    /**
     * @return query string that fully identifies this resource. No ampersand at beginning or end, but always at least one parameter.
     */
    public String getQuery();
    
    /**
     * @return true if current contents differ from repository
     */
    public boolean isChanged();
    
    /**
     * @return save current state of this repository
     */
    public void commitChanges();
    
    /**
     * @return the proper way to open this resource using GET
     */
    public View getRedirectTo();
}
