/* $license_header$
 */
package se.repos.mavenfit.run;

public interface FitRunner {

	/**
	 * Runs FIT tests.
	 * @param to write output to, one piece of info per line
	 * @param testDiry where the FolderRunner should look for test documents
	 * @param reportDiry where the reports should be written
	 */
	public abstract void run(StringBuffer log, String fitTestsDirectory, String reportsDirectory);

}