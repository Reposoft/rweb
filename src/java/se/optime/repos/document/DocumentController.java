/*
 * Created on 2004-okt-02
 */
package se.optime.repos.document;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.web.servlet.ModelAndView;

import se.optime.repos.WebResource;
import se.optime.repos.webdav.RepositoryController;

/**
 * Managing document contents, in particular validating the format.
 * 
 * @author solsson
 * @version $Id$
 */
public class DocumentController extends RepositoryController {

    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryController#handle(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, se.optime.repos.WebResource)
     */
    protected ModelAndView handle(HttpServletRequest request, HttpServletResponse response, WebResource resource) throws Exception {
        return new ModelAndView("document.edit",RESOURCE_NAME,resource);
    }

}
