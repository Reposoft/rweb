/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNNotifyListener;
import org.tigris.subversion.svnclientadapter.SVNNodeKind;

/**
 * Simply a wrapper for the ISVNNotifyListener interface, for feedback to the calling application.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface NotifyListener extends ISVNNotifyListener {
	
    /**
     * called at the beginning of the command
     * @param commandLine
     */
    public void logCommandLine(String commandLine);
    
    /**
     * called multiple times during the execution of a command
     * @param message
     */
    public void logMessage(String message);
    
    /**
     * called when an error happen during a command
     * @param message
     */
    public void logError(String message);

    /**
     * Called when a command has completed to report
     * that the command completed against the specified
     * revision.
     *  
     * @param revision 
     * @param path - path to folder which revision is reported (either root, or some of svn:externals)
     */
    public void logRevision(long revision, String path);

    /**
     * called when a command has completed
     * @param message
     */    
    public void logCompleted(String message);

    /**
     * called when a subversion action happen on a file (add, delete, update ...)
     * @param path the canonical path of the file or dir
     * @param kind file or dir or unknown
     */
    public void onNotify(File path, SVNNodeKind kind);	
}
