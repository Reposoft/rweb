/* $license_header$
 */
package se.repos.mavenfit.run;

import java.io.IOException;
import java.text.ParseException;

import fitlibrary.runner.FolderRunner;

/**
 * Run FIT inside maven plugin environment.
 * 
 * Assumes that it is initialized in the same classloader that the FIT tests should use.
 *
 * @author solsson
 * @since 2006 jul 8
 * @version $Id$
 */
public class FitRunnerImpl implements FitRunner {
	
	/* (non-Javadoc)
	 * @see se.repos.mavenfit.run.FitRunner#run(java.lang.String, java.lang.String)
	 */
	public void run(StringBuffer log, String fitTestsDirectory, String reportsDirectory) {
		FolderRunner folderRunner = new FolderRunner();
		folderRunner.addTestListener(new DefaultTestListener(log));
		try {
			folderRunner.run(fitTestsDirectory, reportsDirectory);
		} catch (ParseException e) {
			throw new RuntimeException("ParseException handling missing", e);
		} catch (IOException e) {
			throw new RuntimeException("IOException handling missing", e);
		}
	}
}
