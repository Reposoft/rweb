/* $license_header$
 */
package se.repos.mavenfit;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.lang.reflect.Method;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLClassLoader;
import java.util.List;

import org.apache.maven.plugin.AbstractMojo;
import org.apache.maven.plugin.MojoExecutionException;
import org.apache.maven.plugin.MojoFailureException;
import org.codehaus.classworlds.ClassRealm;
import org.codehaus.classworlds.ClassWorld;
import org.codehaus.classworlds.DuplicateRealmException;

import se.repos.mavenfit.run.DefaultTestListener;
import se.repos.mavenfit.run.FitRunnerImpl;

/**
 * Runs a FIT test suite as if it was a JUnit test suite.
 * @goal fit
 * @description Runs a FIT test suite as if it was a JUnit test suite
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
	 * Set this to true to continue build even if there are FIT failures.
	 * 
	 * @parameter expression="${maven.test.failure.ignore}"
	 */
	private boolean fitTestFailureIgnore;

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
	 * The file to write FIT output (for example System.out) to.
	 * 
	 * @parameter expression="${project.build.directory}/fit-reports/output.txt"
	 */
	private File fitOutputTextfile;
	
	/**
	 * The directory containing the FIT tests.
	 * 
	 * @parameter expression="${basedir}/src/test/fit"
	 * @required
	 */
	private File fitSourceDirectory;

	/**
	 * Starts a new classloader and launches FitRunner in it.
	 */
	public void execute() throws MojoExecutionException, MojoFailureException {
		
		if (skip) {
			getLog().info("The 'skip' flag is set to true. FIT will not be run.");
			return;
		}
		
		// create classworld classloader
		ClassLoader pluginClassLoader = this.getClass().getClassLoader();
		ClassWorld classWorld = new ClassWorld();
		ClassRealm fitRealm;
		try {
			getLog().debug("This plugin's classloader is: " + pluginClassLoader);
			fitRealm = classWorld.newRealm("mavenfit.runner");
			getLog().debug("FIT will run with classloader: " + fitRealm + ", ID=" + fitRealm.getId());
		} catch (DuplicateRealmException e2) {
			getLog().error("Could not create classworld realm, so no runtime environment for FIT. The plugin will exit now.", e2);
			return;
		}
		
		// add the project's classpath to the new classloader
		for (String url : classpathElements) {
			try {
				URL u = new File(url).toURL();
				getLog().debug("Adding classpath entry: " + u);
				fitRealm.addConstituent(u);
			} catch (MalformedURLException e1) {
				getLog().warn("Can not make URL from classpath element: " + url);
			}
		}
		
		// add the current environment so it does not have to be added as dependencies in the running project
		URL[] pluginClasspath = getClasspath(pluginClassLoader);
		for (URL url : pluginClasspath) {
			getLog().debug("Adding classpath entry: " + url);
			fitRealm.addConstituent(url);
		}
		
		// initialize the classloader from the realm with complete classpath
		ClassLoader classLoader = fitRealm.getClassLoader();
		// Set this classloader in the thread
		// This makes Spring's ClassPathXmlApplicationContext use the same classloader
		Thread.currentThread().setContextClassLoader(classLoader);
		
		// start FitRunner using reflection (because it is in the new classloader)
		final StringBuffer testLog = new StringBuffer();
		try {
			Class fitRunnerClass = classLoader.loadClass(FitRunnerImpl.class.getName());
			Object[] args = new Object[] {testLog, fitSourceDirectory.getAbsolutePath(), reportsDirectory.getAbsolutePath()};
			Method runMethod = fitRunnerClass.getMethod("run", getArgTypes(args));
			Object fitRunnerInstance = fitRunnerClass.newInstance();
			runMethod.invoke(fitRunnerInstance, args);
			getLog().info(testLog.toString());
		} catch (ClassNotFoundException e1) {
			getLog().error("FitRunner class not found in the new classloader", e1);
		} catch (RuntimeException re) {
			getLog().error("FitRunner execution ended with unhandled exception", re);
		} catch (Exception e) {
			throw new RuntimeException("Reflection error '" + e.getMessage() + "'. Can not start FitRunner.", e);
		}
		
		// create a file with the output. FolderRunner only creates test result pages.
		printOutput(testLog, fitOutputTextfile);
		
		// fail build if there are test errors
		if (!fitTestFailureIgnore && testLog.indexOf(DefaultTestListener.OUTPUT_IN_CASE_OF_TESTFAILURES) > -1) {
			throw new MojoFailureException("There were FIT test failures. See reports in\n" + reportsDirectory);
		}
	}
	
	private void printOutput(StringBuffer testLog, File file) {
		try {
			FileWriter fw = new FileWriter(file);
			BufferedWriter out = new BufferedWriter(fw);
			out.write(testLog.toString());
			out.close();
			fw.close();
		} catch (Exception e) {
			getLog().error("Could not write output to file", e);
		}
		
	}

	/**
	 * Gets the classpath of a classloader if it is a URLClassLoader
	 * @param classLoader
	 * @return The classpath entries
	 */
	private URL[] getClasspath(ClassLoader classLoader) {
		if (this.getClass().getClassLoader() instanceof URLClassLoader) {
			return ((URLClassLoader) classLoader).getURLs();
		}
		getLog().error("Plugin classloader is not an URLClassLoader. Can not get the classpath. " +
				"FitRunner classpath will not include the current classloader's classpath entries.");
		return new URL[] {};
	}
	
	/**
	 * Helper for reflection method resolution
	 * @param args Non-null arguments that will be used to call the method
	 * @return The argument types, needed to reflect using Class.getMethod
	 */
	private Class[] getArgTypes(Object[] args) {
		Class[] types = new Class[args.length];
		for (int i = 0; i < args.length; i++) {
			types[i] = args[i].getClass();
		}
		return types;
	}
	
}
