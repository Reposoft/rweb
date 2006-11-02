/* $license_header$
 */
package se.repos.svn.test;

import java.io.File;
import java.util.LinkedList;
import java.util.List;

import org.tigris.subversion.svnclientadapter.SVNNodeKind;

import se.repos.svn.checkout.NotifyListener;

public class TestNotifyListener implements NotifyListener {

	public final List errors = new LinkedList();
	
	public void logCommandLine(String commandLine) {
	}

	public void logCompleted(String message) {
	}

	public void logError(String message) {
		errors.add(message);
		// Thread.dumpStack();
	}

	public void logMessage(String message) {
	}

	public void logRevision(long revision, String path) {
	}

	public void onNotify(File path, SVNNodeKind kind) {
	}

	public void setCommand(int command) {
	}

}
