/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav;

import java.net.URL;

import javax.servlet.ServletRequest;
import javax.servlet.http.HttpServletRequest;

import org.springframework.beans.MutablePropertyValues;
import org.springframework.beans.PropertyValues;
import org.springframework.web.bind.ServletRequestDataBinder;

import se.optime.repos.webdav.RepositoryPath;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryDataBinder extends ServletRequestDataBinder {

    private static final String SHARED_EXTENSION = ".jwa";

    public RepositoryDataBinder(java.lang.Object target, java.lang.String objectName) {
        super(target, objectName);
    }

    /* (non-Javadoc)
     * @see org.springframework.web.bind.ServletRequestDataBinder#bind(javax.servlet.ServletRequest)
     */
    public void bind(ServletRequest request) {
        super.bind(getResourceUrl(
                ((HttpServletRequest)request).getRequestURI()));
        super.bind(request);
        if (super.getTarget() instanceof RepositoryPath) {
            URL url = ((RepositoryPath)super.getTarget()).getUrl();
            super.bind(this.getResourcePath(url));
        }
    }
    
    public PropertyValues getResourceUrl(String requestUrl) {
        String uri = requestUrl.substring(0,requestUrl.length()-SHARED_EXTENSION.length());
        MutablePropertyValues pvs = new MutablePropertyValues();
        pvs.addPropertyValue("url",uri);
        return pvs;
    }
    
    public PropertyValues getResourcePath(URL repositoryUrl) {
        MutablePropertyValues pvs = new MutablePropertyValues();
        String commaSeparatedPath = repositoryUrl.getPath().replace('/',',');
        pvs.addPropertyValue("directories",commaSeparatedPath);
        return pvs;
    }
}
