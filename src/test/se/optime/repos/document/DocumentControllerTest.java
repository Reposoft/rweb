/*
 * Created on 2004-okt-10
 */
package se.optime.repos.document;

import org.springframework.mock.web.MockHttpServletRequest;
import org.springframework.mock.web.MockHttpServletResponse;
import org.springframework.web.servlet.ModelAndView;

import se.optime.repos.StubWebResource;
import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class DocumentControllerTest extends TestCase {

    /*
     * Class under test for ModelAndView handle(HttpServletRequest, HttpServletResponse, WebResource)
     */
    public void testHandleHttpServletRequestHttpServletResponseWebResource() {
        // set up
        DocumentController c = new DocumentController();
        MockHttpServletRequest request = new MockHttpServletRequest();
        MockHttpServletResponse response = new MockHttpServletResponse();
        StubWebResource resource = new StubWebResource();
        try {
            ModelAndView mv = c.handle(request,response,resource);
            assertNotNull("Need view name",mv.getViewName());
        } catch (Exception e) {
            fail("Controller threw exception");
        }
    }

}
