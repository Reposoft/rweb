/* $license_header$
 */
package se.repos.svn.checkout.client;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.tigris.subversion.svnclientadapter.ISVNPromptUserPassword;

/**
 * Maintains Repos conventions by automatic answers to some svn dialogs.
 * <ul>
 * <li>Certificates are accepted permanently if they can be,
 *  because we don't authenticate with certificates, we only want encrypted transfer.</li>
 * <li>Password promts are not answered, becuase if first login attempt is not correct,
 * an exception is thrown by the Repos client.</li>
 * <li>SSH questions are rejected, becuase we never use the svn+ssh protocol.</li>
 * </ul>
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class DefaultAuthenticationReply implements ISVNPromptUserPassword {

	final Logger logger = LoggerFactory.getLogger(DefaultAuthenticationReply.class);
	
	/**
	 * If it is allowed to Accept permanently, do that.
	 * If only temporary accept is allowed, don't accept
	 * because that would be the same as a permanent accept.
	 */
	public int askTrustSSLServer(String info, boolean allowPermanently) {
		logger.info("Client asks if SSL cetificate can be trusted: {}", info);
		if (allowPermanently) {
			logger.info("Accepting certificate permanently");
			return AcceptPermanently;
		}
		logger.info("Rejecting certificate, because it is not reliable");
		return Reject;
	}
	
	// --- Where applicable, do like DefaultPromptUserPassword ---
	
	// this seems to be the prompt for a password
	public boolean prompt(String realm, String username, boolean maySave) {
		// assuming that the connection was attempted with UserCredentials, a prompt means that they were invalid
		// this interface does not allow checked exceptions here, so we'll just reject the prompt
		logger.warn("Returned 'false' for password prompt");
		return false;
	}
	
	// this seems to be the prompt for a username
	public boolean promptUser(String realm, String username, boolean maySave) {
		logger.error("Method DefaultAuthenticationReply#promptUser not implemented. Returning false.");
		return false;
	}
	
	public String getPassword() {
		logger.error("Method DefaultAuthenticationReply#getPassword not implemented. Returning empty string.");
		return "";
	}
	
	public String getUsername() {
		logger.error("Method DefaultAuthenticationReply#getUsername not implemented. Returning empty string.");
		return "";
	}
	
	public String askQuestion(String realm, String question,
			boolean showAnswer, boolean maySave) {
		logger.error("Method DefaultAuthenticationReply#askQuestion not implemented. Returing empty string.");
		return "";
	}

	public boolean askYesNo(String realm, String question, boolean yesIsDefault) {
		logger.error("Method DefaultAuthenticationReply#askYesNo not implemented. Returning " + yesIsDefault);
		return yesIsDefault;
	}

	public boolean userAllowedSave() {
		// don't know what this does, but since we don't return a username here we don't want it saved
		logger.warn("Returned 'false' for userAllowedSave");
		return false;
	}

	public int getSSHPort() {
		throw new UnsupportedOperationException("Subversion SSH protocol is not supported");
	}
	
	public boolean promptSSH(String realm, String username, int sshPort, boolean maySave) {
		throw new UnsupportedOperationException("Subversion SSH protocol is not supported");
	}

	public String getSSHPrivateKeyPassphrase() {
		throw new UnsupportedOperationException("Authentication with SSH client certificate is not supported");
	}

	public String getSSHPrivateKeyPath() {
		throw new UnsupportedOperationException("Authentication with SSH client certificate is not supported");
	}

	public String getSSLClientCertPassword() {
		throw new UnsupportedOperationException("Authentication with SSH client certificate is not supported");
	}

	public String getSSLClientCertPath() {
		throw new UnsupportedOperationException("Authentication with SSH client certificate is not supported");
	}
	
	public boolean promptSSL(String realm, boolean maySave) {
		throw new UnsupportedOperationException("Authentication with SSH client certificate is not supported");
	}

}
