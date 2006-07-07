/* $license_header$
 */
package se.repos.mavenfit;

import java.io.File;
import java.io.IOException;
import java.lang.reflect.Constructor;
import java.lang.reflect.InvocationTargetException;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLClassLoader;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.List;

import org.apache.maven.plugin.AbstractMojo;
import org.apache.maven.plugin.MojoExecutionException;
import org.apache.maven.plugin.MojoFailureException;

import fitlibrary.runner.FolderRunner;
import fitlibrary.runner.Report;

/**
 * @goal fit
 * @description Run fit
 * @requiresDependencyResolution test
 * @execute phase=test-compile
 */
public class FolderRunnerMojo extends AbstractMojo {

	/**
	 * Set this to 'true' to bypass unit tests entirely. Its use is NOT
	 * RECOMMENDED, but quite convenient on occasion.
	 * 
	 * @parameter expression="${maven.test.skip}"
	 */
	private boolean skip;

	/**
	 * Set this to true to ignore a failure during testing. Its use is NOT
	 * RECOMMENDED, but quite convenient on occasion.
	 * 
	 * @parameter expression="${maven.test.failure.ignore}"
	 */
	private boolean testFailureIgnore;

	/**
	 * The base directory of the project being tested. This can be obtained in
	 * your unit test by System.getProperty("basedir").
	 * 
	 * @parameter expression="${basedir}"
	 * @required
	 */
	private File basedir;

	/**
	 * The directory containing generated classes of the project being tested.
	 * 
	 * @parameter expression="${project.build.outputDirectory}"
	 * @required
	 */
	private File classesDirectory;

	/**
	 * The directory containing generated test classes of the project being
	 * tested.
	 * 
	 * @parameter expression="${project.build.testOutputDirectory}"
	 * @required
	 */
	private File fixtureClassesDirectory;

	/**
	 * The classpath elements of the project being tested.
	 * 
	 * @parameter expression="${project.testClasspathElements}"
	 * @required
	 * @readonly
	 */
	private List<String> classpathElements;

	/**
	 * Base directory where all reports are written to.
	 * 
	 * @parameter expression="${project.build.directory}/fit-reports"
	 */
	private File reportsDirectory;

	/**
	 * The directory containing the FIT tests.
	 * 
	 * @parameter expression="${basedir}/src/test/fit"
	 * @required
	 */
	private File fitSourceDirectory;

	public void execute() throws MojoExecutionException, MojoFailureException {
		
		List<URL> classpathUrls = new ArrayList<URL>(classpathElements.size());
		for (String url : classpathElements) {
			getLog().debug("  " + url);
			File f = new File(url);
			try {
				getLog().debug("Adding classpath entry: " + f);
				classpathUrls.add(f.toURL());
			} catch (MalformedURLException e) {
				getLog().warn(
						"Could not add classpath entry URL for file: " + f);
			}
		}

		IsolatedClassLoader classLoader = new IsolatedClassLoader(classpathUrls);
		Class folderRunnerClass;
		try {
			folderRunnerClass = classLoader.loadClass("fitlibrary.runner.FolderRunner");
		} catch (ClassNotFoundException e1) {
			getLog().error("FolderRunner class not found. Is fitlibraryRunner.jar in the classpath?", e1);
			return;
		}
		
		String[] args = new String[] {fitSourceDirectory.getAbsolutePath(), reportsDirectory.getAbsolutePath()};
		
		Constructor folderRunnerConstructor;
		try {
			folderRunnerConstructor = folderRunnerClass.getDeclaredConstructor(args.getClass());
		} catch (SecurityException e1) {
			throw new RuntimeException("SecurityException handling missing", e1);
		} catch (NoSuchMethodException e1) {
			throw new RuntimeException("Can not find constructor that takes a String[]", e1);
		}

		Object folderRunner;
		try {
			folderRunner = folderRunnerConstructor.newInstance(new Object[] {args});
		} catch (IllegalArgumentException e1) {
			throw new RuntimeException("IllegalArgumentException handling missing", e1);
		} catch (InstantiationException e1) {
			throw new RuntimeException("InstantiationException handling missing", e1);
		} catch (IllegalAccessException e1) {
			throw new RuntimeException("IllegalAccessException handling missing", e1);
		} catch (InvocationTargetException e1) {
			throw new RuntimeException("InvocationTargetException handling missing", e1);
		}
		
		Object report;
		try {
			report = folderRunner.getClass().getMethod("run").invoke(folderRunner);
			report.getClass().getMethod("exit").invoke(report);
		} catch (IllegalArgumentException e) {
			throw new RuntimeException("IllegalArgumentException handling missing", e);
		} catch (SecurityException e) {
			throw new RuntimeException("SecurityException handling missing", e);
		} catch (IllegalAccessException e) {
			throw new RuntimeException("IllegalAccessException handling missing", e);
		} catch (InvocationTargetException e) {
			throw new RuntimeException("InvocationTargetException handling missing", e);
		} catch (NoSuchMethodException e) {
			throw new RuntimeException("NoSuchMethodException handling missing", e);
		}
	}

	private class IsolatedClassLoader extends URLClassLoader {
		public IsolatedClassLoader(List<URL> classpathUrls) {
			super(classpathUrls.toArray(new URL[]{}), getSystemClassLoader());
		}
		// seems maven can not compile this if it is Java5-compliant
		protected synchronized Class loadClass(String name, boolean resolve) throws ClassNotFoundException {
			return super.loadClass(name, resolve);
		}
	}

}
