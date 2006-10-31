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
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ConfigFile extends IniFile {

	// sections
	private static final String MISCELLANY = "miscellany";
	
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
		IniEditor config = load();
		if (!config.hasSection(section)) {
			config.addSection(section);
		}
		config.set(section, option, value);
		save(config);
	}
	
	/**
	 * @param globalIgnores [miscellany] global-ignores
	 */
	public void setGlobalIgnores(String globalIgnores) {
		this.set(MISCELLANY, "global-ignores", globalIgnores);
	}
}
