/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.validation.BindException;
import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.mvc.AbstractCommandController;

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
        // TODO Auto-generated method stub
        throw new java.lang.UnsupportedOperationException(
                "Method RepositoryCommandController.handle not implemented");
    }
    
    protected abstract ModelAndView handle(HttpServletRequest request,
            HttpServletResponse response, RepositoryPath command)
            throws Exception;
}
