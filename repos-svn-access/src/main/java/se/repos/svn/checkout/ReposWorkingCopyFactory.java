/* $license_header$
 */
package se.repos.svn.checkout;

import se.repos.svn.ClientProvider;
import se.repos.svn.checkout.client.ConflictHandler;
import se.repos.svn.checkout.client.ConflictHandlerStandard;
import se.repos.svn.checkout.client.ReposWorkingCopySvnAnt;
import se.repos.svn.javasvn.TmateSvnClientProvider;

/**
 * A factory to instantiate a default ReposWorkingCopy implementatoin,
 * for applications that don't use dependency injection.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @todo implement check that the client provider library exists, see ClientProvider interface
 */
public abstract class ReposWorkingCopyFactory {

    // the client pool/factory. could be injected
    private static ClientProvider DEFAULT_CLIENT_PROVIDER = new TmateSvnClientProvider();
	
    /**
     * Creates a working copy instance and sets a {@link ConflictHandler} to it.
     * @param settings
     * @return
     */
	public static ReposWorkingCopy getClient(CheckoutSettings settings) {
		ReposWorkingCopySvnAnt wc = new ReposWorkingCopySvnAnt(DEFAULT_CLIENT_PROVIDER, settings);
		wc.setConflictHandler(new ConflictHandlerStandard());
		return wc;
	}
}
