/* $license_header$
 */
package se.repos.format.web;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.context.ApplicationContext;
import org.springframework.context.support.ClassPathXmlApplicationContext;
import org.springframework.core.io.Resource;

/**
 * Decoding wiki syntax to strict XHTML.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class DecodeServlet extends HttpServlet {

	private static final long serialVersionUID = 1L;
	
	ApplicationContext applicationContext = getContext();
	
	/**
	 * Shows a basic XHTML-strict form to input wiki syntax and to
	 * show the fields needed for POST.
	 */
	@Override
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		Resource html = getWikiInputForm();
		//response.setHeader("LastModified", file last modified date );
		InputStream in = html.getInputStream();
		printHtmlToResponse(in, response);
	}

	private void printHtmlToResponse(InputStream html, HttpServletResponse response) throws IOException {
		InputStream in = html;
		OutputStream out = response.getOutputStream();
		int c;
		while ((c = in.read()) >= 0) {
			out.write(c);
		}
	}

	private ApplicationContext getContext() {
		return new ClassPathXmlApplicationContext("se/repos/format/context.xml");
	}

	private Resource getWikiInputForm() throws IOException {
		Resource page = applicationContext.getResource("se/repos/format/web/Input.html");
		return page;
	}

	@Override
	protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		response.getWriter().print(request.getParameter("text"));
	}

	
}
