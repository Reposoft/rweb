/* $license_header$
 */
package se.repos.issu.fixtures;

import fit.ColumnFixture;

public class SquareFixture extends ColumnFixture {

	public int n;
	
	public String square() {
		return Integer.toString(n * n);
	}
}
