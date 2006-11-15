/* $license_header$
 */
package se.repos.svn.config.file;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.LinkedList;
import java.util.List;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

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
	
	// values
	private static final String MI_GLOBAL_IGNORES = "global-ignores";
	
	/**
	 * 
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
}
