/*
 * Created on 2004-okt-09
 */
package se.optime.repos.webdav;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.mock.web.MockHttpServletRequest;
import org.springframework.mock.web.MockHttpServletResponse;
import org.springframework.validation.BindException;
import org.springframework.validation.Errors;
import org.springframework.web.servlet.ModelAndView;

import se.optime.repos.RepositoryPath;
import se.optime.repos.StubRepositoryPath;
import se.optime.repos.StubWebResource;
import se.optime.repos.WebResource;
import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryControllerTest extends TestCase {
    
    /**
     * Template code to set up MVC mocks, for copy-and-paste
     */
    public void testHandle_template() {
        // set up
        RepositoryController c = new DummyRepositoryController();
        MockHttpServletRequest request = new MockHttpServletRequest();
        MockHttpServletResponse response = new MockHttpServletResponse();
        RepositoryPath path = new StubRepositoryPath();
        BindException errors = new BindException(path,"path");
    }
    
    /*
     * Class under test for ModelAndView handle(HttpServletRequest, HttpServletResponse, Object, BindException)
     */
    public void testHandleBindError() {
        // set up
        RepositoryController c = new DummyRepositoryController();
        MockHttpServletRequest request = new MockHttpServletRequest();
        MockHttpServletResponse response = new MockHttpServletResponse();
        RepositoryPath path = new StubRepositoryPath();
        BindException errors = new BindException(path,"path");
        // test
        errors.reject("houston...", "name");
        try {
            c.handle(request,response,path,errors);
            fail("Bind errors should be thrown");
        } catch (Exception e) {
            assertTrue("Errors implementation",e instanceof Errors);
        }
    }

    public void testHandleDirectory() {
        RepositoryController c = new DummyRepositoryController();
        ModelAndView mv = null;
        try {
            StubWebResource wr = new StubWebResource();
            wr.setTestValues();
            wr.setHref(null);
            mv = c.handleDirectory(null,null,wr);
        } catch (Exception e) {
            fail("Base controller should handle directory without errors but threw " + e);
        }
        assertNotNull("Need something to show the user",mv);
    }
    
    private class DummyRepositoryController extends RepositoryController {
        private boolean handled = false;
        /* (non-Javadoc)
         * @see se.optime.repos.webdav.RepositoryController#handle(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, se.optime.repos.WebResource)
         */
        protected ModelAndView handle(HttpServletRequest request, HttpServletResponse response, WebResource resource) throws Exception {
            handled = true;
            return null;
        }
    }

}
