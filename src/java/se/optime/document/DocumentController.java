/*
 * Created on Sep 8, 2004
 */
package se.optime.document;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.mvc.Controller;

/**
 * @author solsson
 * @version $Id$
 */
public class DocumentController implements Controller {

    /* (non-Javadoc)
     * @see org.springframework.web.servlet.mvc.Controller#handleRequest(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse)
     */
    public ModelAndView handleRequest(HttpServletRequest request, HttpServletResponse response) throws Exception {
         
        String xuri = request.getRequestURI();
        String uri = xuri.substring(0,xuri.lastIndexOf('.'));
        return new ModelAndView("document/edit","url",uri);
        
    }

}
