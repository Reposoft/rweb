/*
 * Created on 2004-okt-05
 */
package se.optime.repos.webdav.action;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.View;

import se.optime.repos.WebResource;
import se.optime.repos.webdav.RepositoryController;

/**
 * @author solsson
 * @version $Id$
 */
public abstract class RepositoryActionController extends
        RepositoryController {

    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryController#handle(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, se.optime.repos.WebResource)
     */
    protected ModelAndView handle(HttpServletRequest request,
            HttpServletResponse response, WebResource resource)
            throws Exception {
        return execute(request,response,resource);
    }
    
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryController#handleDirectory(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, se.optime.repos.WebResource)
     */
    protected ModelAndView handleDirectory(HttpServletRequest request,
            HttpServletResponse response, WebResource resource)
            throws Exception {
        return execute(request,response,resource);
    }
    
    /**
     * Verify command type and forward to subclass
     * @return The view returned from subclass, with empty model.
     */
    protected ModelAndView execute(HttpServletRequest request,
            HttpServletResponse response, WebResource resource)
			throws Exception {
        	RepositoryUpdate command = (RepositoryUpdate)resource;
        	checkForward(request, command);
        	View view = execute(request,command);
        	return new ModelAndView(view);
    }
    
    /**
     * @param request
     * @param command Make sure this has some place to redirect to
     */
    private void checkForward(HttpServletRequest request, RepositoryUpdate command) {
        if (command.getForward()==null) {
            String referrer = request.getHeader("Referer");
            if (referrer==null) {
                logger.warn("WebDAV action called without 'forward' set and without referer. Redirection to repository.");
                referrer = command.getURL().toString();
            }
            command.setForward(referrer);
        }
    }

    protected abstract View execute(HttpServletRequest request, RepositoryUpdate resource)
    		throws Exception;
}
