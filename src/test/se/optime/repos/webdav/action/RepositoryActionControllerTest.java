/*
 * Created on 2004-okt-10
 */
package se.optime.repos.webdav.action;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.mock.web.MockHttpServletRequest;
import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.View;
import org.springframework.web.servlet.view.RedirectView;

import se.optime.repos.WebResource;

import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryActionControllerTest extends TestCase {

    /*
     * Class under test for ModelAndView execute(HttpServletRequest, HttpServletResponse, WebResource)
     */
    
    public void testCheckForward() {
        RepositoryActionController c = new TestableActionController();
        MockHttpServletRequest request = new MockHttpServletRequest();
        RepositoryUpdate update = new RepositoryUpdate();
        
        // do easymock dynamock
        
    }
    
    public void testCheckForwardInvalid() {
        
    }
    
    public void testExecute() {
        
    }
    
    private class TestableActionController
    		extends RepositoryActionController {

        private boolean executed = false;
        
        public void checkForward(HttpServletRequest request,
                RepositoryUpdate command) {
            super.checkForward(request,command);
        }
        
        /* (non-Javadoc)
         * @see se.optime.repos.webdav.action.RepositoryActionController#execute(javax.servlet.http.HttpServletRequest, se.optime.repos.webdav.action.RepositoryUpdate)
         */
        protected View execute(HttpServletRequest request, RepositoryUpdate resource) throws Exception {
            executed = true;
            return new RedirectView("test");
        }
    }

}
