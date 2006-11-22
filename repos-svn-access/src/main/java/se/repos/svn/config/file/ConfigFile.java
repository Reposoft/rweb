/* $license_header$
 */
package se.repos.svn.config.file;

import java.io.File;
import java.io.IOException;

import se.repos.svn.config.ConfigurationStateException;
import ch.ubique.inieditor.IniEditor;

/**
 * Models the "config" file in the configuration area.
 * 
 * See README.txt in a sunversion configuration area for syntax.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ConfigFile extends IniFile {

	// sections
	private static final String MISCELLANY = "miscellany";
	private static final String AUTH = "auth";
	
	// values
	private static final String MI_GLOBAL_IGNORES = "global-ignores";
	private static final String AUTH_STORE_PASSWORDS = "store-passwords";
	private static final String AUTH_STORE_PASSWORDS_DEFAULT = "yes";
	
	/**
	 * Sets file location and validates current contents.
	 * @param iniFile The file in the runtime configuration area
	 * @throws IOException If the file can not be read
	 * @throws ConfigurationStateException If the file is empty or contents are not valid configuration
	 */
	public ConfigFile(File iniFile) throws IOException, ConfigurationStateException {
		super(iniFile);
	}
	
	private void set(String section, String option, String value) {
		// first enable section if it exists but is commented out
		if ( ! super.replaceLine("^#+\\s*\\["+section+"\\].*", "["+section+"]")) {
			IniEditor config = load();
			if (!config.hasSection(section)) {
				config.addSection(section);
			}
			save(config);
		}
		// add value
		IniEditor config = load();
		if (config.hasOption(section, option) ||
				! super.appendAfterLine("^#+\\s*"+option+"\\s*=.*", option + " = " + value, section)) {
			config.set(section, option, value);
			save(config);
		}
	}
	
	/**
	 * @param globalIgnores [miscellany] global-ignores
	 */
	public void setGlobalIgnores(String globalIgnores) {
		this.set(MISCELLANY, MI_GLOBAL_IGNORES, globalIgnores);
	}
	
	/**
	 * @return the property value if the property exists, null otherwise
	 */
	public String getGlobalIgnores() {
		IniEditor config = load();
		if (config.hasOption(MISCELLANY, MI_GLOBAL_IGNORES)) {
			return config.get(MISCELLANY, MI_GLOBAL_IGNORES);
		} else {
			return null;
		}
	}
	
	public void setStorePasswords(String value) {
		this.set(AUTH, AUTH_STORE_PASSWORDS, value);
	}
	
	public String getStorePasswords() {
		IniEditor config = load();
		if (config.hasOption(AUTH, AUTH_STORE_PASSWORDS)) {
			return config.get(AUTH, AUTH_STORE_PASSWORDS);
		} else {
			return AUTH_STORE_PASSWORDS_DEFAULT;
		}
	}
}
