/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.validation.BindException;
import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.mvc.AbstractCommandController;

import se.optime.repos.webdav.RepositoryPath;

/**
 * @author solsson
 * @version $Id$
 */
public abstract class RepositoryCommandController extends AbstractCommandController {

    /* (non-Javadoc)
     * @see org.springframework.web.servlet.mvc.AbstractCommandController#handle(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, java.lang.Object, org.springframework.validation.BindException)
     */
    protected ModelAndView handle(HttpServletRequest request,
            HttpServletResponse response, Object command, BindException errors)
            throws Exception {
        if (errors.hasErrors())
            throw errors;
        if ("POST".equals(request.getMethod()))
            return save(request,(RepositoryPath)command);
        return show(request, response, (RepositoryPath)command);
    }
    
    protected abstract ModelAndView show(HttpServletRequest request,
            HttpServletResponse response, RepositoryPath resource)
            throws Exception;
    
    protected abstract ModelAndView save(HttpServletRequest request,
            RepositoryPath resource)
            throws Exception;
}
