/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav;

import javax.servlet.ServletRequest;

import org.springframework.web.bind.ServletRequestDataBinder;


/**
 * Custom binding of ResourcePath, currently not needed and not employed.
 * @author solsson
 * @version $Id$
 */
public class RepositoryDataBinder extends ServletRequestDataBinder {

    public RepositoryDataBinder(java.lang.Object target, java.lang.String objectName) {
        super(target, objectName);
    }

    /* (non-Javadoc)
     * @see org.springframework.web.bind.ServletRequestDataBinder#bind(javax.servlet.ServletRequest)
     */
    public void bind(ServletRequest request) {
        super.bind(request);
    }

}
