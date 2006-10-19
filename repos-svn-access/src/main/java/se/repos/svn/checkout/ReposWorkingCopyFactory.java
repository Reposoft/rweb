/* $license_header$
 */
package se.repos.svn.checkout;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import se.repos.svn.ClientProvider;
import se.repos.svn.ClientProvider.ClientNotAvaliableException;
import se.repos.svn.checkout.client.ConflictHandler;
import se.repos.svn.checkout.client.ConflictHandlerStandard;
import se.repos.svn.checkout.client.ReposWorkingCopySvnAnt;
import se.repos.svn.javahl.JavahlClientProvider;
import se.repos.svn.javasvn.TmateSvnClientProvider;

/**
 * A factory to instantiate a default ReposWorkingCopy implementatoin,
 * for applications that don't use dependency injection.
 * 
 * Should verify that the required client libraries are available.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo implement check that the client provider library exists, see ClientProvider interface
 */
public abstract class ReposWorkingCopyFactory {

	private static Logger logger = LoggerFactory.getLogger(ReposWorkingCopyFactory.class);
	
	private static ClientProvider client = null;
	
    /**
     * Creates a working copy instance and sets a {@link ConflictHandler} to it.
     * @param settings
     * @return
     */
	public static ReposWorkingCopy getClient(CheckoutSettings settings) {
		ReposWorkingCopySvnAnt wc = new ReposWorkingCopySvnAnt(
				getClientProvider(), 
				settings,
				new ConflictHandlerStandard());
		logger.info("Created new Repos working copy instance.");
		return wc;
	}
	
	/**
	 * Creates the javahl client if available.
	 * Last resort is the JavaSVN client, that needs a license for redistribution.
	 * @throws RuntimeException if there is no client library available. This is considered a deployment issue.
	 */
	private static ClientProvider getClientProvider() throws RuntimeException {
		if (client!=null) return client; // javachl can not be initialize twice
		try {
			client = new JavahlClientProvider();
			logger.info("Using Javahl client library. See license: http://subversion.tigris.org/license-1.html");
			return client;
		} catch (ClientNotAvaliableException e) {
			logger.info("Javahl client library is not available.");
		}
	    // try the pure java library. it can be installed by simply adding the jar.
	    client = new TmateSvnClientProvider();
	    if (client!=null) {
	    	logger.warn("Using the Tmate SVN library. For commercial use this requires a license. See http://tmate.org/.");
	    	return client;
	    }
	    throw new RuntimeException("There is no SVN client avaliable. Tried Javahl and JavaSVN.");
	}
}
