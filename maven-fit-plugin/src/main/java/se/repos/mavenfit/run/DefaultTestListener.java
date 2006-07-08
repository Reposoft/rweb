/* $license_header$
 */
package se.repos.mavenfit.run;

import java.io.Serializable;

import fitlibrary.runner.TestListener;

/**
 * Prints as much FIT output as possible to a string, in a formatted manner.
 * @author solsson
 * @since 2006 jul 8
 * @version $Id$
 */
public class DefaultTestListener implements TestListener, Serializable {

	/**
	 * Can be searched for in the output to figure out if there were test errors.
	 * Value: {@value #OUTPUT_IN_CASE_OF_TESTFAILURES}
	 */
	public static final String OUTPUT_IN_CASE_OF_TESTFAILURES = "There were test failures. ";

	private static final long serialVersionUID = 1L;

	StringBuffer log;

	boolean complete = false;

	boolean failed = false;

	public DefaultTestListener(final StringBuffer log) {
		this.log = log;
		log.append('\n').append("---------------- FIT output -----------------")
				.append('\n');
	}

	/**
	 * Prints the System.out from fixtures and the system-under-test logs.
	 */
	public void reportOutput(String name, String out, String output) {
		if (output == null) {
			return;
		}
		String print = "   " + output.trim().replace("\n", "\n   ");
		String outType = getOutputType(out);

		log.append("---- " + name + " (" + outType + ") ----").append('\n');
		log.append(print).append('\n');
	}

	private String getOutputType(String out) {
		if ("out".equals(out)) {
			return "fixture output";
		}
		return "other output";
	}

	/**
	 * Prints a line that the run is completed.
	 */
	public void suiteComplete() {
		this.complete = true;
		log.append("-------- FIT testsuite run completed --------").append('\n');
	}

	/**
	 * Don't know when FolderRunner calls this method.
	 * @param pageCounts Not printed
	 * @param assertionCounts Printed every time
	 */
	public void testComplete(boolean failing, String pageCounts,
			String assertionCounts) {
		if ("0 right, 0 wrong, 0 ignored, 0 exceptions".equals(assertionCounts)) {
			return;
		}
		this.failed = failing;
		if (failed) {
			log.append(OUTPUT_IN_CASE_OF_TESTFAILURES);
		} else {
			log.append("Tests passed. ");
		}
		log.append(assertionCounts).append('\n');
	}

	/**
	 * @return True if the test run is completed
	 */
	public boolean isComplete() {
		return complete;
	}

	/**
	 * @return True if there were any test failures
	 */
	public boolean isFailed() {
		return failed;
	}
}
