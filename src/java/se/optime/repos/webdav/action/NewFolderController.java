/*
 * Created on 2004-okt-05
 */
package se.optime.repos.webdav.action;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.web.servlet.ModelAndView;

import se.optime.repos.WebResource;

/**
 * @author solsson
 * @version $Id$
 */
public class NewFolderController extends RepositoryActionController {

    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryController#handle(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, se.optime.repos.WebResource)
     */
    protected ModelAndView handle(HttpServletRequest request, HttpServletResponse response, WebResource resource) throws Exception {
        throw new UnsupportedOperationException("Method NewFolderController#handle not implemented yet.");
    }

}
