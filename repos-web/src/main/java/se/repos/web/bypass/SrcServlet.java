/* $license_header$
 */
package se.repos.web.bypass;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import se.repos.web.contents.RepositoryResourceStreamLocator;

import wicket.util.resource.IResourceStream;
import wicket.util.resource.ResourceStreamNotFoundException;
import wicket.util.resource.UrlResourceStream;

/**
 * Handles the relative links to 'src', such as images.
 *
 * @author solsson
 * @since 2006 jul 11
 * @version $Id$
 */
public class SrcServlet extends HttpServlet {

	private final Logger logger = LoggerFactory.getLogger(SrcServlet.class);
	
	private static final long serialVersionUID = 1L;

	//@Override
	protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
		String path = req.getServletPath();
		if (path.endsWith("/")) {
			resp.setHeader("Location", req.getContextPath() + path + "index.html");
			return;
		}
		IResourceStream resourceStream = new UrlResourceStream(RepositoryResourceStreamLocator.getURL(path));
		InputStream in;
		try {
			in = resourceStream.getInputStream();
		} catch (ResourceStreamNotFoundException e) {
			logger.error("Could not open static resource {}", path);
			return;
		}
		OutputStream out = resp.getOutputStream();
		int c;
		while ((c = in.read()) >= 0) {
			out.write(c);
		}
	}
}
