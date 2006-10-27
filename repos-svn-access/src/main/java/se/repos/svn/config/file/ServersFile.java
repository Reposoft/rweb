/* $license_header$
 */
package se.repos.svn.config.file;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;

import se.repos.svn.config.ConfigurationUpdateException;

import ch.ubique.inieditor.IniEditor;

/**
 * Models the 'servers' file in the runtime configuration area.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ServersFile extends IniFile {

	public static final String GROUP_GLOBAL = "global";
	protected static final String GROUP_GROUPS = "groups";
	
	public ServersFile(File iniFile) throws FileNotFoundException, IOException {
		super(iniFile);
		verifyServerFile(iniFile.getAbsolutePath());
	}
	
	private void verifyServerFile(String fileDescriptionForErrorMessages) throws IOException {
		IniEditor file = load();
		if (!file.hasSection(GROUP_GROUPS)) throw new IOException(fileDescriptionForErrorMessages + " must have a configuration section: " + GROUP_GROUPS);
		if (!file.hasSection(GROUP_GLOBAL)) throw new IOException(fileDescriptionForErrorMessages + " must have a configuration section: " + GROUP_GLOBAL);
	}
	
	/**
	 * @param group new server group that should be added as a section to the ini file
	 */
	public void addGroup(String groupName, String domainNameMatch) {
		if (GROUP_GROUPS.equals(groupName)) throw new ConfigurationUpdateException(groupName + " is not a valid group nane.");
		IniEditor servers = load();
		if (servers.hasSection(groupName)) throw new ConfigurationUpdateException("Can not add section '" + groupName + "' because it already exists.");
		servers.set(GROUP_GROUPS, groupName, domainNameMatch);
		servers.addSection(groupName);
		save(servers);
	}
	
	/**
	 * @param group group name or GROUP_GLOBAL
	 * @param host
	 */
	public void setProxyHost(String group, String host) {
		IniEditor servers = load();
		if (!servers.hasSection(group)) throw new ConfigurationUpdateException("The group '" + group + "' does not exist in the servers file");
		servers.set(group, "http-proxy-host", host);
		save(servers);
	}
	
	public void setProxyPort(String group, String port) {
		IniEditor servers = load();
		if (!servers.hasSection(group)) throw new ConfigurationUpdateException("The group '" + group + "' does not exist in the servers file");
		servers.set(group, "http-proxy-port", port);
		save(servers);
	}
	
	public void setProxyUsername(String group, String username) {
		IniEditor servers = load();
		if (!servers.hasSection(group)) throw new ConfigurationUpdateException("The group '" + group + "' does not exist in the servers file");
		servers.set(group, "http-proxy-username", username);
		save(servers);
	}
	
	public void setProxyPassword(String group, String password) {
		IniEditor servers = load();
		if (!servers.hasSection(group)) throw new ConfigurationUpdateException("The group '" + group + "' does not exist in the servers file");
		servers.set(group, "http-proxy-password", password);
		save(servers);
	}
}
