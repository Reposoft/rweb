/* $license_header$
 */
package se.repos.svn.config.file;

import java.io.File;
import java.io.IOException;

import se.repos.svn.SvnProxySettings;
import se.repos.svn.config.ConfigurationStateException;
import se.repos.svn.config.ConfigurationUpdateException;

import ch.ubique.inieditor.IniEditor;

/**
 * Models the 'servers' file in the runtime configuration area.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ServersFile extends IniFile {

	private static final String HTTP_PROXY_PASSWORD = "http-proxy-password";
	private static final String HTTP_PROXY_USERNAME = "http-proxy-username";
	private static final String HTTP_PROXY_PORT = "http-proxy-port";
	private static final String HTTP_PROXY_HOST = "http-proxy-host";
	public static final String GROUP_GLOBAL = "global";
	protected static final String GROUP_GROUPS = "groups";
	
	/**
	 * @param iniFile The 'servers' file in the runtime configuration area
	 * @throws IOException If the file can not be read
	 * @throws ConfigurationStateException If the file is empty or contents are not valid configuration
	 */
	public ServersFile(File iniFile) throws IOException, ConfigurationStateException {
		super(iniFile);
		verifyServerFile(iniFile.getAbsolutePath());
	}
	
	private void verifyServerFile(String fileDescriptionForErrorMessages) throws ConfigurationStateException {
		IniEditor file = load();
		if (!file.hasSection(GROUP_GROUPS)) throw new ConfigurationStateException(
				fileDescriptionForErrorMessages + " must have a configuration section: " + GROUP_GROUPS);
		if (!file.hasSection(GROUP_GLOBAL)) throw new ConfigurationStateException(
				fileDescriptionForErrorMessages + " must have a configuration section: " + GROUP_GLOBAL);
	}
	
	/**
	 * @param groupName new server group that should be added as a section to the ini file
	 * @param domainNameMatch the qualifier for this group
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
	 * @param timeout in seconds to wait for server response in the group
	 */
	public void setHttpTimeout(String group, int timeout) {
		setValue(group, "http-timeout", Integer.toString(timeout));
	}

	/**
	 * @param group group name or GROUP_GLOBAL
	 * @param compress true to allow DAV request compression
	 */
	public void setHttpCompression(String group, boolean compress) {
		setValue(group, "http-compression", compress ? "yes" : "no");
	}
	
	/**
	 * @param group group name or GROUP_GLOBAL
	 * @param settings the proxy for the given group
	 */
	public void setProxySettings(String group, SvnProxySettings settings) {
		setProxyHost(group, settings.getHost());
		setProxyPort(group, settings.getPort());
		if (settings.getUsername() != null) setProxyUsername(group, settings.getUsername());
		if (settings.getPassword() != null) setProxyPassword(group, settings.getPassword());
	}
	
	private void setProxyHost(String group, String host) {
		setValue(group, HTTP_PROXY_HOST, host);
	}
	
	private void setProxyPort(String group, String port) {
		setValue(group, HTTP_PROXY_PORT, port);
	}
	
	private void setProxyUsername(String group, String username) {
		setValue(group, HTTP_PROXY_USERNAME, username);
	}
	
	private void setProxyPassword(String group, String password) {
		setValue(group, HTTP_PROXY_PASSWORD, password);
	}
	
	private void setValue(String group, String option, String value) {
		IniEditor servers = load();
		if (!servers.hasSection(group)) throw new ConfigurationUpdateException("The group '" + group + "' does not exist in the servers file");
		servers.set(group, option, value);
		save(servers);
	}

	public SvnProxySettings getProxySettings(String group) {
		IniEditor servers = load();
		if (!servers.hasSection(group)) throw new ConfigurationUpdateException("The group '" + group + "' does not exist in the servers file");
		if (!servers.hasOption(group, HTTP_PROXY_HOST)) {
			return SvnProxySettings.NOPROXY;
		}
		String host = servers.get(group, HTTP_PROXY_HOST);
		int port = SvnProxySettings.NOPORT;
		try {
			port = Integer.parseInt(servers.get(group, HTTP_PROXY_PORT));
		} catch (NumberFormatException e) {
			throw new RuntimeException("Could not parse proxy port value: " + e.getMessage(), e);
		}
		SvnProxySettings p = new SvnProxySettings(host, port);
		if (servers.hasOption(group, HTTP_PROXY_USERNAME)) {
			p.setUsername(servers.get(group, HTTP_PROXY_USERNAME));
		}
		if (servers.hasOption(group, HTTP_PROXY_PORT)) {
			p.setPassword(servers.get(group, HTTP_PROXY_PASSWORD));
		}
		return p;
	}	
	
}
