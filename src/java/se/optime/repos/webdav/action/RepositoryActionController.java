/*
 * Created on 2004-okt-05
 */
package se.optime.repos.webdav.action;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.web.servlet.ModelAndView;

import se.optime.repos.RepositoryPath;
import se.optime.repos.webdav.RepositoryController;

/**
 * @author solsson
 * @version $Id$
 */
public abstract class RepositoryActionController extends
        RepositoryController {

    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryCommandController#show(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, se.optime.repos.webdav.RepositoryPath)
     */
    protected ModelAndView show(HttpServletRequest request,
            HttpServletResponse response, RepositoryPath resource)
            throws Exception {
        throw new UnsupportedOperationException(
                "Method RepositoryActionController#show not implemented yet.");
    }

}
