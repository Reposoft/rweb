/*
 * Created on Sep 8, 2004
 */
package se.optime.document;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.util.HashMap;
import java.util.Map;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.core.io.Resource;
import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.mvc.Controller;

import se.optime.repos.webdav.WebRepository;

/**
 * @author solsson
 * @version $Id$
 */
public class DocumentController implements Controller {

    private WebRepository repository = null;
    
    /* (non-Javadoc)
     * @see org.springframework.web.servlet.mvc.Controller#handleRequest(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse)
     */
    public ModelAndView handleRequest(HttpServletRequest request, HttpServletResponse response) throws Exception {
               
        //String xuri = request.getRequestURI();
        String xuri = request.getRequestURL().toString();
        // query strings are not handled
        String uri = xuri.substring(0,xuri.lastIndexOf('.'));
        
        Map model = new HashMap();
        model.put("url",uri);
        Resource file = repository.getCurrentVersion(uri);
        InputStreamReader isr = new InputStreamReader(file.getInputStream());
        BufferedReader reader = new BufferedReader(isr);
        
        model.put("contents",reader.readLine());
        

        return new ModelAndView("document/edit");
        
    }

    /**
     * @param repository The repository to set.
     */
    public void setRepository(WebRepository repository) {
        this.repository = repository;
    }
}
