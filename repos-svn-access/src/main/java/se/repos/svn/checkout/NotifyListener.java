/* $license_header$
 */
package se.repos.svn.checkout;

import org.tigris.subversion.svnclientadapter.ISVNNotifyListener;

/**
 * Simply a wrapper for the ISVNNotifyListener interface, for feedback to the calling application.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface NotifyListener extends ISVNNotifyListener {
}
