/*
 * Created on Sep 8, 2004
 */
package se.optime.document;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.HashMap;
import java.util.Map;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;
import org.springframework.core.io.Resource;
import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.mvc.Controller;

import se.optime.repos.user.BasicAuthenticationResolver;
import se.optime.repos.webdav.WebRepository;
import sun.misc.BASE64Decoder;

/**
 * @author solsson
 * @version $Id$
 */
public class DocumentController implements Controller {

    private WebRepository repository = null;
    private BasicAuthenticationResolver authenticationResolver = new BasicAuthenticationResolver();
    
    protected final Log logger = LogFactory.getLog(this.getClass());
    
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
//        Resource file = repository.getCurrentVersion(uri);
//        InputStreamReader isr = new InputStreamReader(file.getInputStream());
//        BufferedReader reader = new BufferedReader(isr);
        
        logger.info("Request by " + authenticationResolver.getAuthenticatedUsername() + ":" + authenticationResolver.getAuthenticatedPassword() + " = " + authenticationResolver.getBasicAuthenticationString());
        
        model.put("contents","lite text"); //reader.readLine());
        
        return new ModelAndView("document/edit");
        
    }
    
    protected String getAuthorization(String auth) {
        if (auth == null) return "";  // no auth

        if (!auth.toUpperCase().startsWith("BASIC ")) 
          return "";  // we only do BASIC

        // Get encoded user and password, comes after "BASIC "
        String userpassEncoded = auth.substring(6);

        // Decode it, using any base 64 decoder
        sun.misc.BASE64Decoder dec = new sun.misc.BASE64Decoder();
        String userpassDecoded = "";
        try {
            userpassDecoded = new String(dec.decodeBuffer(userpassEncoded));
        } catch (IOException e) {
            
        }
        
        // userpassDecoded = "Username:pAsSwrD"
        return userpassDecoded;
    }    

    /**
     * @param repository The repository to set.
     */
    public void setRepository(WebRepository repository) {
        this.repository = repository;
    }
}
