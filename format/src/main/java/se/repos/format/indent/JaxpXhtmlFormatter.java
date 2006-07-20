/* $license_header$
 */
package se.repos.format.indent;

import java.io.Writer;

import javax.xml.transform.OutputKeys;
import javax.xml.transform.Result;
import javax.xml.transform.Source;
import javax.xml.transform.Transformer;
import javax.xml.transform.TransformerConfigurationException;
import javax.xml.transform.TransformerException;
import javax.xml.transform.TransformerFactory;
import javax.xml.transform.TransformerFactoryConfigurationError;
import javax.xml.transform.stream.StreamResult;

/**
 * Uses standard Java API for XML Prodcesing to format xml.
 * 
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class JaxpXhtmlFormatter implements XhtmlFormatter {

	private static final String OUTPUT_ENCODING = "UTF-8";

	public void format(Source validXhtml, Writer indentedXhtmlOutput) {

		// Create an "identity" transformer - copies input to output
		Transformer t;
		try {
			t = TransformerFactory.newInstance().newTransformer();
		} catch (TransformerConfigurationException e) {
			throw new RuntimeException("Fatal error. Could not configure XML serializer.", e);
		} catch (TransformerFactoryConfigurationError e) {
			throw new RuntimeException("Fatal error. Could not get transformer factory for XML serializer.", e);
		}

		// for "XHTML" serialization, use the output method "xml"
		// and set publicId as shown
		t.setOutputProperty(OutputKeys.METHOD, "xml");
		t.setOutputProperty(OutputKeys.DOCTYPE_PUBLIC,
				"-//W3C//DTD XHTML 1.0 Strict//EN");
		t.setOutputProperty(OutputKeys.DOCTYPE_SYSTEM,
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd");
		t.setOutputProperty(OutputKeys.ENCODING, OUTPUT_ENCODING);
		t.setOutputProperty(OutputKeys.INDENT, "yes");
		

		// Serialize DOM tree
		Result r = new StreamResult(indentedXhtmlOutput);
		try {
			t.transform(validXhtml, r);
		} catch (TransformerException e) {
			// TODO auto-generated
			throw new RuntimeException(
					"TransformerException thrown, not handled", e);
		}

	}

}
