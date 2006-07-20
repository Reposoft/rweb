/* $license_header$
 */
package se.repos.format.indent;

import static org.junit.Assert.*;

import org.junit.Test;

public abstract class AbstractXhtmlFormatterTest {
	
	abstract XhtmlFormatter getXhtmlFormatter();
	
	@Test
	protected void runAllIndentTests() {
		XhtmlFormatter formatter = getXhtmlFormatter();
		// run test on every .in+.out file pair in test/resources
		
	}
}
