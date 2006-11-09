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

	public int getSSHPort() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#getSSHPort not implemented yet");
		}
		return 0;
	}

	public String getSSHPrivateKeyPassphrase() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#getSSHPrivateKeyPassphrase not implemented yet");
		}
		return null;
	}

	public String getSSHPrivateKeyPath() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#getSSHPrivateKeyPath not implemented yet");
		}
		return null;
	}

	public String getSSLClientCertPassword() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#getSSLClientCertPassword not implemented yet");
		}
		return null;
	}

	public String getSSLClientCertPath() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#getSSLClientCertPath not implemented yet");
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

	public boolean prompt(String realm, String username, boolean maySave) {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#prompt not implemented yet");
		}
		return false;
	}

	public boolean promptSSH(String realm, String username, int sshPort,
			boolean maySave) {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#promptSSH not implemented yet");
		}
		return false;
	}

	public boolean promptSSL(String realm, boolean maySave) {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#promptSSL not implemented yet");
		}
		return false;
	}

	public boolean userAllowedSave() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method DefaultAuthenticationReply#userAllowedSave not implemented yet");
		}
		return false;
	}

}
