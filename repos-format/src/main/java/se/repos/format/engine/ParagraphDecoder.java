/* $license_header$
 */
package se.repos.format.engine;

import org.apache.commons.codec.Decoder;
import org.apache.commons.codec.DecoderException;

/**
 * Converts simple text files to XHTML paragraphs.
 * 
 * Single lines without comma or point will be interpreted as headline.
 * If it is the first line it will be h1, all the others will be h2.
 * 
 * Note that the codecs will only generate body contents. No HTML headers.
 *
 * Use SAX events
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ParagraphDecoder implements Decoder {

	public Object decode(Object arg0) throws DecoderException {
		String line = getNextLine(arg0);
		return "<h1>" + line + "</h1>";
	}

	private String getNextLine(Object arg0) {
		return "" + arg0;
	}

}
