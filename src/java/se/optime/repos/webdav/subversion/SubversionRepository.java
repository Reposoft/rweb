/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav.subversion;

import org.springframework.context.ResourceLoaderAware;
import org.springframework.core.io.Resource;
import org.springframework.core.io.ResourceLoader;

import se.optime.repos.webdav.RevisionRepository;

/**
 * @author solsson
 * @version $Id$
 */
public class SubversionRepository implements RevisionRepository, ResourceLoaderAware {

    private ResourceLoader resourceLoader;

    /* (non-Javadoc)
     * @see se.optime.repos.webdav.WebdavRepository#getCurrentVersion(java.lang.String)
     */
    public Resource getCurrentVersion(String fileUrl) {
        return resourceLoader.getResource(fileUrl);
    }

    /* (non-Javadoc)
     * @see org.springframework.context.ResourceLoaderAware#setResourceLoader(org.springframework.core.io.ResourceLoader)
     */
    public void setResourceLoader(ResourceLoader resourceLoader) {
        this.resourceLoader = resourceLoader;
    }

}
