/* $license_header$
 */
package se.repos.svn.config.file;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.util.LinkedList;
import java.util.List;
import java.util.regex.Pattern;

import ch.ubique.inieditor.IniEditor;

/**
 * Reads and writes ini-file with sections.
 * 
 * Not thread safe.
 * 
 * Warning: It looks like the IniEditor library fails silently on parser errors, 
 * for example if a value occurs twice in the same section.
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
	
	private File getConfigFile() {
		return configFile;
	}
	
	private List getFileLines() {
		List lines = new LinkedList();
		try {
			FileReader fr = new FileReader(getConfigFile());
			BufferedReader reader = new BufferedReader(fr);
			String line;
			while (( line = reader.readLine()) != null){
		        lines.add(line);
			}
			reader.close();
			fr.close();
		} catch (FileNotFoundException e) {
			// TODO auto-generated
			throw new RuntimeException("FileNotFoundException thrown, not handled", e);
		} catch (IOException e) {
			// TODO auto-generated
			throw new RuntimeException("IOException thrown, not handled", e);
		}
		return lines;
	}
	
	private void write(List lines) {
		try {
			FileWriter fw = new FileWriter(getConfigFile());
			BufferedWriter bw = new BufferedWriter(fw);
			PrintWriter writer = new PrintWriter(bw);
			for (int i = 0; i < lines.size(); i++) {
				writer.println(lines.get(i).toString());
			}
			writer.close();
			bw.close();
			fw.close();
		} catch (IOException e) {
			// TODO auto-generated
			throw new RuntimeException("IOException thrown, not handled", e);
		}
	}
	
	protected boolean replaceLine(String regex, String replacement) {
		List lines = getFileLines();
		int n = getLineNumberForMatch(lines, regex);
		if (n < 0) return false;
		lines.set(n, replacement);
		write(lines);
		return true;
	}
	
	protected boolean appendAfterLine(String regex, String append) {
		List lines = getFileLines();
		int n = getLineNumberForMatch(lines, regex);
		if (n < 0) return false;
		lines.add(n+1, append);
		write(lines);
		return true;
	}
	
	/**
	 * Append value, but only if the regex matches inside an active section
	 * @param regex
	 * @param append
	 * @param section
	 * @return
	 */
	protected boolean appendAfterLine(String regex, String append, String section) {
		List lines = getFileLines();
		int s = getLineNumberForMatch(lines, getSectionRegex(section));
		int ss = getLineNumberForMatch(lines, "^#*\\[.*\\].*", s+1);
		int n = getLineNumberForMatch(lines, regex);
		if (n < 0) return false;
		if (n > ss) return false;
		lines.add(n+1, append);
		write(lines);
		return true;
	}

	private String getSectionRegex(String sectionName) {
		return "^\\["+Pattern.quote(sectionName)+"\\]\\s*$";
	}
	
	protected int getLineNumberForMatch(List stringList, String regex) {
		return getLineNumberForMatch(stringList, regex, 0);
	}
	
	protected int getLineNumberForMatch(List stringList, String regex, int startIndex) {
		for (int i = startIndex; i < stringList.size(); i++) {
			if (Pattern.matches(regex, stringList.get(i).toString())) return i;
		}
		return -1;
	}

}