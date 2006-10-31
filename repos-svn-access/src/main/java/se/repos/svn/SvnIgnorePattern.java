/* $license_header$
 */
package se.repos.svn;

import java.io.File;
import java.util.Arrays;
import java.util.List;

/**
 * Every ignore entry is encapsulated in a class, to provide validation.
 *
 * Immutable.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class SvnIgnorePattern {

	private String value;
	
	public SvnIgnorePattern(File child) {
		this.value = child.getName();
	}
	
	public SvnIgnorePattern(String string) {
		this.value = string.trim();
	}

	/**
	 * @return trimmed string representaion of the ignore
	 */
	public String getValue() {
		return value;
	}

	/**
	 * @return the value
	 */
	public String toString() {
		return getValue();
	}	
	
	/**
	 * @return true if the argument is an {@link SvnIgnorePattern} and value equals this object's value.
	 */
	public boolean equals(Object svnIgnorePattern) {
		if (svnIgnorePattern == null) return false;
		return toString().equals(svnIgnorePattern.toString());
	}

	/**
	 * The ignore property's value is a whitespace separated list of values.
	 * Some client libraries, like svnClientAdapter, return this as a list of strings.
	 * @param propertyStringList List with 0 or more String values.
	 * @return The same values as typed immutable instances
	 */
	public static SvnIgnorePattern[] array(List propertyStringList) {
		SvnIgnorePattern[] a = new SvnIgnorePattern[propertyStringList.size()];
		for (int i=0; i<propertyStringList.size(); i++) {
			if (!(propertyStringList.get(i) instanceof String)) throw new IllegalArgumentException("List element number " + i + " is not a String");
			a[i] = new SvnIgnorePattern(propertyStringList.get(i).toString());
		}
		return a;
	}
	
}
