/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.util.Date;

import org.tigris.subversion.svnclientadapter.ISVNStatus;
import org.tigris.subversion.svnclientadapter.SVNNodeKind;
import org.tigris.subversion.svnclientadapter.SVNStatusKind;
import org.tigris.subversion.svnclientadapter.SVNUrl;
import org.tigris.subversion.svnclientadapter.SVNRevision.Number;

/**
 * Returned as ISVNStatus if status is requested for a resource that does not exist.
 * 
 * If if does not exist svn command line will only return a status row
 * if the file was versioned (so now it is missing).
 * 
 * Returns UNVERSIONED for {@link #getTextStatus()} and NONE for {@link #getPropStatus()}.
 * Instantiated with the missing File, which is returned on {@link #getFile()} and {@link #getPath()}.
 * Throws {@link UnsupportedOperationException} for all the other methods.
 *
 * It should be quite rare that an application asks for the status of a non-existing
 * file unless it is known to be versioned.
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
class StatusUnversionedMissing implements ISVNStatus {

	private File file;

	public StatusUnversionedMissing(File file) {
		this.file = file;
	}
	
	public SVNStatusKind getTextStatus() {
		return SVNStatusKind.UNVERSIONED;
	}	
	
	public SVNStatusKind getPropStatus() {
		return SVNStatusKind.NONE;
	}
	
	public File getFile() {
		return file;
	}
	
	public String getPath() {
		return getFile().getPath();
	}
	
	public File getConflictNew() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getConflictNew not implemented yet");
		}
		return null;
	}

	public File getConflictOld() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getConflictOld not implemented yet");
		}
		return null;
	}

	public File getConflictWorking() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getConflictWorking not implemented yet");
		}
		return null;
	}

	public Date getLastChangedDate() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getLastChangedDate not implemented yet");
		}
		return null;
	}

	public Number getLastChangedRevision() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getLastChangedRevision not implemented yet");
		}
		return null;
	}

	public String getLastCommitAuthor() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getLastCommitAuthor not implemented yet");
		}
		return null;
	}

	public String getLockComment() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getLockComment not implemented yet");
		}
		return null;
	}

	public Date getLockCreationDate() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getLockCreationDate not implemented yet");
		}
		return null;
	}

	public String getLockOwner() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getLockOwner not implemented yet");
		}
		return null;
	}

	public SVNNodeKind getNodeKind() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getNodeKind not implemented yet");
		}
		return null;
	}

	public SVNStatusKind getRepositoryPropStatus() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getRepositoryPropStatus not implemented yet");
		}
		return null;
	}

	public SVNStatusKind getRepositoryTextStatus() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getRepositoryTextStatus not implemented yet");
		}
		return null;
	}

	public Number getRevision() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getRevision not implemented yet");
		}
		return null;
	}

	public SVNUrl getUrl() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getUrl not implemented yet");
		}
		return null;
	}

	public SVNUrl getUrlCopiedFrom() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getUrlCopiedFrom not implemented yet");
		}
		return null;
	}

	public String getUrlString() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#getUrlString not implemented yet");
		}
		return null;
	}

	public boolean isCopied() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#isCopied not implemented yet");
		}
		return false;
	}

	public boolean isSwitched() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#isSwitched not implemented yet");
		}
		return false;
	}

	public boolean isWcLocked() {
		if (true) {
			throw new UnsupportedOperationException(
					"Method StatusUnversionedMissing#isWcLocked not implemented yet");
		}
		return false;
	}

}
