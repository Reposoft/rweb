/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav;

import java.net.URL;

import org.springframework.core.io.Resource;

/**
 * @author solsson
 * @version $Id$
 */
public interface RepositoryPath {
    
    public URL getUrl();
    
    public Resource getResource();
    
}
