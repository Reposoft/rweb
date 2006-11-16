/* $license_header$
 */
package se.repos.svn.checkout.client;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.tigris.subversion.svnclientadapter.ISVNPromptUserPassword;

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
	
	public boolean prompt(String realm, String username, boolean maySave) {
		// assuming that the connection was attempted with UserCredentials, a prompt means that they were invalid
		// this interface does not allow checked exceptions here, so we'll just reject the prompt
		return false;
	}
	
	public String askQuestion(String realm, String question,
			boolean showAnswer, boolean maySave) {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#askQuestion not implemented yet");
		}
		return null;
	}

	public boolean askYesNo(String realm, String question, boolean yesIsDefault) {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#askYesNo not implemented yet");
		}
		return false;
	}

	public String getPassword() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#getPassword not implemented yet");
		}
		return null;
	}
	
	public String getUsername() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#getUsername not implemented yet");
		}
		return null;
	}

	public boolean userAllowedSave() {
		// don't know what this does, but since we don't return a username here we don't want it saved
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
