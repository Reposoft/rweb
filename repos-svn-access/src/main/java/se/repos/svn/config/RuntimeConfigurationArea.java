/* $license_header$
 */
package se.repos.svn.config;

import java.io.File;
import java.io.IOException;
import java.lang.reflect.Method;
import java.util.Arrays;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.tigris.subversion.svnclientadapter.AbstractClientAdapter;

import se.repos.svn.SvnIgnorePattern;
import se.repos.svn.SvnProxySettings;
import se.repos.svn.config.file.ConfigFile;
import se.repos.svn.config.file.ServersFile;

/**
 * Abstraction for a the subversion client configuration area.
 * 
 * Contains the logic to decide where to store settings (which folder and which file).
 * Does not support windows registry.
 * <p>
 * This implementation assumes that a standard SVN client has created the runtime
 * configuratin area before configuration operations are attempted.
 * This class never creates configuration files, only changes them.
 * <p>
 * If configuration is invalid when read, RuntimeExceptions will be thrown
 * (same as for unexpected IOErrors). To avoid unchecked errors, make sure that the constructor
 * is called with a configuration area that is already created, because then
 * validation is done immediately and exceptions are declared. After that it is
 * assumed that configuration is only changed using this instance, so further
 * validation should not be required.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class RuntimeConfigurationArea implements ClientConfiguration {
	
	protected static final Logger logger = LoggerFactory.getLogger(RuntimeConfigurationArea.class);
	
	private File folder;
	private boolean validated = false;
	
	private static final String DELIMITER = "\\s+";
	
	private static final String TRUE = "yes";
	private static final String FALSE = "no";
	
	private static final String getBooleanValue(boolean flag) {
		if (flag) return TRUE;
		return FALSE;
	}
	
	private static final boolean getBoolean(String value) {
		// documented in README.txt in suversion config area
		if (TRUE.equalsIgnoreCase(value)) return true;
		if ("on".equalsIgnoreCase(value)) return true;
		if ("1".equalsIgnoreCase(value)) return true;
		return false;
	}

	/**
	 * Reads configuration from the default subversion user folder
	 * @throws ConfigurationStateException 
	 */
	public RuntimeConfigurationArea() throws ConfigurationStateException {
		this(getConfigFolder());
	}
	
	/**
	 * 
	 * @param configFolder The SVN client's runtime configuration area.
	 * @throws ConfigurationStateException 
	 */
	public RuntimeConfigurationArea(File configFolder) throws ConfigurationStateException {
		if (configFolder.exists()) {
			validate(configFolder);
		}
		this.folder = configFolder;
	}
	
	/**
	 * Validate current configureation, if not validated already.
	 * @throws ConfigurationStateException if not valid
	 */
	void validate() throws ConfigurationStateException {
		if (!validated) validate(getFolder());
	}

	/**
	 * Validate current configuration, even if validated flag is true
	 * @param configFolder Folder that should exist and look like a standard svn runtime configuration area
	 * @throws ConfigurationStateException if it is not valid
	 */
	private void validate(File configFolder) throws ConfigurationStateException {
		if (!configFolder.exists()) throw new ConfigurationStateException(
				"Subversion configuration area " + configFolder + " does not exist. " +
				"Has the subversion client been run with this folder as ---config-dir yet?");
		if (!configFolder.isDirectory()) throw new ConfigurationStateException("Coniguration area " + configFolder + " is not a folder");
		// do the check that is never done in the getters
		File c = getConfigFile(configFolder);
		File s = getServersFile(configFolder);
		if (!c.exists()) throw new ConfigurationStateException("File 'config' not found in runtime configuration area folder " + configFolder);
		if (!s.exists()) throw new ConfigurationStateException("File 'servers' not found in runtime configuration area folder " + configFolder);
		// run the validation of the specific files
		try {
			this.getConfig(c);
			this.getServers(s);
		} catch (Throwable e) {
			throw new ConfigurationStateException("Invalid configuration area " + configFolder, e);
		}
		// ok. normally we expect that validation is only needed once.
		validated = true;
	}

	/**
	 * @return loaded and validated 'config' file from current runtime configuration area
	 */
	private ConfigFile getConfig() {
		try {
			validate(); // never return config that has not been validated
			return getConfig(getConfigFile(getFolder()));
		} catch (ConfigurationStateException e) {
			throw new RuntimeException("Could not load Subversion 'config' file", e);
		}
	}
	
	private ConfigFile getConfig(File configFile) throws ConfigurationStateException {
		try {
			return new ConfigFile(configFile);
		} catch (IOException e) {
			throw new RuntimeException("Error reading Subversion 'config' file", e);
		}
	}

	/**
	 * @return loaded and validated 'servers' file from current runtime configuration area
	 */
	private ServersFile getServers() {
		try {
			validate(); // never return config that has not been validated
			return getServers(getServersFile(getFolder()));
		} catch (ConfigurationStateException e) {
			throw new RuntimeException("Could not load Subversion 'servers' file", e);
		}
	}

	private ServersFile getServers(File serversFile) throws ConfigurationStateException {
		try {
			return new ServersFile(serversFile);
		} catch (IOException e) {
			throw new RuntimeException("Error reading Subversion 'servers' file", e);
		}
	}	
	
	/**
	 * @return runtime configuration area
	 */
	private File getFolder() {
		return folder;
	}
	
	private File getServersFile(File configFolder) {
		return new File(configFolder, "servers");
	}

	private File getConfigFile(File configFolder) {
		return new File(configFolder, "config");
	}

	/**
	 * from interface
	 */
	public void addGlobalIgnore(SvnIgnorePattern pattern) {
		setGlobalIgnores(getGlobalIgnores(), pattern);
	}

	/**
	 * @param patterns
	 * @param added extra pattern
	 */
	private void setGlobalIgnores(SvnIgnorePattern[] patterns, SvnIgnorePattern added) {
		boolean add = true;
		StringBuffer list = new StringBuffer();
		for (int i = 0; i < patterns.length; i++) {
			list.append(" ").append(patterns[i].getValue());
			if (patterns[i].equals(added)) add = false;
		}
		if (add) list.append(" ").append(added.getValue());
		getConfig().setGlobalIgnores(list.substring(1));
	}
	
	public SvnIgnorePattern[] getGlobalIgnores() {
		String e = getConfig().getGlobalIgnores();
		if (e == null) return new SvnIgnorePattern[0];
		return SvnIgnorePattern.array(Arrays.asList(e.split(DELIMITER)));
	}

	public void setProxySettings(SvnProxySettings proxySettings) {
		getServers().setProxySettings(ServersFile.GROUP_GLOBAL, proxySettings);
	}

	public SvnProxySettings getProxySettings() {
		return getServers().getProxySettings(ServersFile.GROUP_GLOBAL);
	}	
	
	public void setStorePasswords(boolean authCache) {
		getConfig().setStorePasswords(RuntimeConfigurationArea.getBooleanValue(authCache));
	}
	
	public boolean isStorePasswords() {
		return RuntimeConfigurationArea.getBoolean(getConfig().getStorePasswords());
	}
	
	/**
	 * ISVNClientAdapter has no method that can report configuration folder, so the logic for that is here.
	 * @return the default SVN client configuration area for the user
	 */
	public static File getConfigFolder() {
		File f = new File(getAppdataFolderForUser(), getSubversionConfigFolderName());
		logger.info("Default subversion configuration is located in {}", f.getAbsolutePath());
		return f;
	}
	
	/**
	 * Gets the folder containing the subversion configuration folder.
	 * 
	 * For non-windows, the user.home folder is used to store configuration folders.
	 * 
	 * In Windows with Java 1.5, System.getenv and APPDATA environment can be used.
	 * For earlier JVMs, a qualified guess is made based on the user.home system property.
	 * If the property can not be found, an error message suggesting an upgrade to Java 1.5 is thrown.
	 * 
	 * @return the current user's application data
	 * @throws RuntimeException if the folder can not be found
	 */
	private static File getAppdataFolderForUser() {
		if (!RuntimeConfigurationArea.isWindows()) {
			return new File(System.getProperty("user.home"));
		}
		try {
	        Method getenv = System.class.getMethod("getenv", new Class[] {String.class});
	        if (getenv != null) {
	            Object appdata = getenv.invoke(null, new Object[] {"APPDATA"});
	            if (appdata != null) {
	                return new File(appdata.toString());
	            }
	        }
		} catch (Exception e) { }
		File guess = new File(System.getProperty("user.home"), "Application Data");
		if (!guess.exists()) throw new RuntimeException("Could not locate application data folder. It might help to upgrade to Java 5.");
		return guess;
	}
	
	/**
	 * Guesses the folder name inside the application data folder for the runtime configuration area.
	 * Guess according to {@link http://svnbook.red-bean.com/nightly/en/svn.advanced.html#svn.advanced.confarea}.
	 * @return folder name
	 */
	private static String getSubversionConfigFolderName() {
		if (isWindows()) {
			return "Subversion";
		} else {
			return ".subversion";
		}
	}
	
	/**
	 * @return true if running on Windows operating system, false for all other
	 */
	private static boolean isWindows() {
		return AbstractClientAdapter.isOsWindows();
	}

}
