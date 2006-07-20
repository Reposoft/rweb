/* $license_header$
 */
package se.repos.format.indent;

import java.io.Writer;

import javax.xml.transform.Source;

/**
 * Add line breaks and indentation to HTML to allow diff.
 * 
 * It is essential for the diff tools in CVS and Subversion to have reliable diff between versions.
 * Proper line breaks makes the diff smaller and more relevant.
 * Consistent indentation ensures that only lines where the contents have changed are included in the diff.
 *
 * Comprehensive unit tests ensure that the formatting rules don't change.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface XhtmlFormatter {

	/**
	 * Format and indent the xhtml
	 * @param validXhtml Treated as XML, so the input format is irrelevant.
	 * @param indentedXhtmlOutput Formatting according to permanent rules.
	 */
	public abstract void format(Source validXhtml, Writer indentedXhtmlOutput);
	
}
