/* $license_header$
 */
package se.repos.format.engine;

import static org.junit.Assert.*;

import org.apache.commons.codec.DecoderException;
import org.junit.Before;
import org.junit.Test;

public class ParagraphDecoderTest {

	ParagraphDecoder paragraphDecoder = null;
	
	@Before
	public void setUp() throws Exception {
		paragraphDecoder = new ParagraphDecoder();
	}

	@Test
	public void testDecodeOneLine() throws DecoderException {
		String text = "This is a headline";
		String expected = "<h1>This is a headline</h1>";
		assertEquals(expected, getHtml(text));
	}

	private String getHtml(String text) throws DecoderException {
		return (String) paragraphDecoder.decode(text);
	}

}
