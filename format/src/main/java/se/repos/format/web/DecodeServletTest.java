/* $license_header$
 */
package se.repos.format.web;

import static org.junit.Assert.*;

import java.io.StringWriter;

import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletResponse;

import org.junit.Before;
import org.junit.Test;

public class DecodeServletTest {

	HttpServlet decodeServlet = null;
	
	@Before
	public void setUp() throws Exception {
		decodeServlet = new DecodeServlet();
	}

	@Test
	public void testDoGetHttpServletRequestHttpServletResponse() {
		StringWriter stringWriter = new StringWriter();
		HttpServletResponse response; //mock
	}

	@Test
	public void testDoPostHttpServletRequestHttpServletResponse() {
		fail("Not yet implemented");
	}

}
