/*
 * Created on 2004-okt-05
 */
package se.optime.repos.calendar;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.web.servlet.ModelAndView;

import se.optime.repos.WebResource;
import se.optime.repos.webdav.RepositoryController;

/**
 * @author solsson
 * @version $Id$
 */
public class IcalController extends RepositoryController {

    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryController#handle(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, se.optime.repos.WebResource)
     */
    protected ModelAndView handle(HttpServletRequest request, HttpServletResponse response, WebResource resource) throws Exception {
        throw new UnsupportedOperationException("Method IcalController#handle not implemented yet.");
    }

}
