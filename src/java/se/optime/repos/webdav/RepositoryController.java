/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.validation.BindException;
import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.mvc.AbstractCommandController;

import se.optime.repos.WebResource;

/**
 * @author solsson
 * @version $Id$
 */
public abstract class RepositoryController extends AbstractCommandController {
    
    /**
     * Default modelName for resource
     * @see org.springframework.web.servlet.ModelAndView#ModelAndView(java.lang.String, java.lang.String, java.lang.Object)
     */
    public static final String RESOURCE_NAME = "resource";
    
    /* (non-Javadoc)
     * @see org.springframework.web.servlet.mvc.AbstractCommandController#handle(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, java.lang.Object, org.springframework.validation.BindException)
     */
    protected ModelAndView handle(HttpServletRequest request,
            HttpServletResponse response, Object command, BindException errors)
            throws Exception {
        if (errors.hasErrors())
            throw errors;
        RepositoryResource resource = (RepositoryResource)command;
        if (!resource.exists())
            throw new DoesNotExistException(resource);
        if (resource.isDirectory())
            return handleDirectory(request, response, resource);
        return handle(request, response, resource);
    }
    
    protected abstract ModelAndView handle(HttpServletRequest request,
            HttpServletResponse response, WebResource resource)
            throws Exception;
    
    /**
     * If the resource is a WebDAV directory, this is the default handling.
     * @param request
     * @param response
     * @param resource
     * @return information to the user
     * @throws Exception
     */
    protected ModelAndView handleDirectory(HttpServletRequest request,
            HttpServletResponse response, WebResource resource)
    		throws Exception {
        return new ModelAndView("directory",RESOURCE_NAME,resource);
    }

}
