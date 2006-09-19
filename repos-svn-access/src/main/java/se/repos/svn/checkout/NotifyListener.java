/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;

import org.tigris.subversion.svnclientadapter.ISVNNotifyListener;
import org.tigris.subversion.svnclientadapter.SVNNodeKind;

/**
 * Allows the calling application to get notified of SVN operations.
 * 
 * Use {@link ReposWorkingCopy#addNotifyListener(NotifyListener)} to get feedback.
 * Simply a wrapper for the ISVNNotifyListener interface, for feedback to the calling application.
 *
 * The svnClientAdapter uses a single threaded model, so for example {@link #setCommand(int)}
 * can be used to save the command type for the nextcoming {@link #onNotify(File, SVNNodeKind)}
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface NotifyListener extends ISVNNotifyListener {

    /**
     * Tell the callback the command to be executed
     * @param command from the enumeration: ISVNNotifyListener.Command
     */
    public void setCommand(int command);	
	
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
     * called when an error happens during a command
     * A conflict is reported as a message "C [file path"
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
     * Called when a subversion action happens on a file (add, delete, update ...)
     * @param path the canonical path of the file or dir
     * @param kind file or dir or unknown
     */
    public void onNotify(File path, SVNNodeKind kind);
}
