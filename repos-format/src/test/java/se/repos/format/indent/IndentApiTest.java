/* $license_header$
 */
package se.repos.format.indent;

import static org.junit.Assert.*;

import java.io.Reader;
import java.io.StringReader;
import java.io.StringWriter;

import javax.xml.transform.Source;
import javax.xml.transform.stream.StreamSource;

import org.junit.Before;
import org.junit.Test;

public class IndentApiTest {

	XhtmlFormatter xhtmlFormatter = null;
	
	@Before
	public void setUp() throws Exception {
		xhtmlFormatter = getDefaultFormatter();
	}

	@Test
	public void testFormatParagraph() {
		String p = "<p>One paragraph</p>";
		assertEquals(
				"<p>One paragraph</p>",
				formatBodyContents(p));
	}

	@Test
	public void testFormatUL() {
		String p = "<ul><li>A</li><li>B</li></ul>";
		assertEquals(
				"<ul>\n<li>A</li>\n<li>B</li>\n</ul>",
				formatBodyContents(p));
	}	
	
	@Test
	public void testFormatEmptyDocument() {
		String p = "<html><head></head><body></body></html>";
		assertEquals(
				"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" +
				"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">" +
				"\n<html>\n<head />\n<body />\n</html>\n",
				formatPage(p));
	}
	
	// For some reason the transformer outputs the DTD into the document in this @Test
	public void testDocumentWithDoctype() {
		String s = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">" +
				"<html xmlns=\"http://www.w3.org/1999/xhtml\">" +
				"<head>" +
				//"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />" +
				"<title>Untitled Document</title>" +
				"</head>" +
				"" +
				"<body>" +
				"</body>" +
				"</html>";
		assertEquals("", formatPage(s));
	}
	
	private String formatBodyContents(String p) {
		String htmlStart = 
			"<html>" +
			"<head><title>test</title></head><body>\n";
		String htmlEnd = 
			"\n</body>\n</html>\n";
		String result = formatPage(htmlStart + p + htmlEnd);
		// remove start and end and their newline characters
		int bodyStart = result.indexOf("<body>") + 7;
		if (bodyStart < 10) throw new RuntimeException("Could not find <body> in: " + result);
		int bodyEnd = result.length() - htmlEnd.length();
		return result.substring(bodyStart, bodyEnd);
	}
	
	private String formatPage(String p) {
		Reader stream = new StringReader(p);
		Source s = new StreamSource(stream);
		StringWriter w = new StringWriter();
		xhtmlFormatter.format(s, w);
		return w.getBuffer().toString().replace("\r\n", "\n");
	}

	private XhtmlFormatter getDefaultFormatter() {
		return new JaxpXhtmlFormatter();
	}
}
