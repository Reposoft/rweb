/* $license_header$
 */
package se.repos.svn.config.file;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;

import ch.ubique.inieditor.IniEditor;

/**
 * Reads and writes ini-file with sections.
 * 
 * Not thread safe.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class IniFile {

	private File configFile;

	public IniFile() {
		super();
	}

	public IniFile(File iniFile) throws FileNotFoundException, IOException {
		if (!iniFile.exists()) throw new FileNotFoundException(iniFile.getAbsolutePath());
		if (!iniFile.isFile()) throw new IOException(iniFile.getAbsolutePath() + " is not a file");
		if (!iniFile.canRead()) throw new IOException(iniFile.getAbsolutePath() + " is not readable");
		if (!iniFile.canWrite()) throw new IOException(iniFile.getAbsolutePath() + " is not writable");
		this.configFile = iniFile;
	}

	protected IniEditor load() {
		IniEditor file = new IniEditor();
		try {
			file.load(configFile);
		} catch (IOException e) {
			// TODO auto-generated
			throw new RuntimeException("IOException thrown, not handled", e);
		}
		return file;
	}

	protected void save(IniEditor file) {
		try {
			file.save(configFile);
		} catch (IOException e) {
			// TODO auto-generated
			throw new RuntimeException("IOException thrown, not handled", e);
		}
	}

}