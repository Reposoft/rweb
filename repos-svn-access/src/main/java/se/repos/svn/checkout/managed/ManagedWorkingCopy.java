/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.NotifyListener;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.ReposWorkingCopyFactory;
import se.repos.svn.checkout.RepositoryAccessException;
import se.repos.svn.checkout.VersionedFileProperties;
import se.repos.svn.checkout.VersionedFolderProperties;
import se.repos.svn.checkout.VersionedProperties;
import se.repos.svn.checkout.WorkingCopyAccessException;
import se.repos.svn.checkout.simple.SimpleWorkingCopy;
import se.repos.svn.config.ClientConfiguration;

/**
 * A basic subversion client, with extra logic for supporting common user operations.
 *
 * "Managed" client means that it is assumed that the user gets some help in maintaining the working copy, so no integrity checks are needed here.
 *
 * <ul>
 * <li>Allows {@link #synchronize(String)} for convenience, and {@link #commit(String)} for speed.
 * <li>Allows move operation on an already moved file or folder.
 * <li>Allows delete on an already deleted file or folder.
 * <li>Allows isVersioned on resources that are not in versioned folder (returns false).
 * </ul>
 * 
 * Enforces client settings with Repos defaults, described in
 * {@link DefaultReposClientSettings}. This is done on checkout.
 * <p>
 * Before a file is changed, it can be reserved using {@see #lock(String)}.
 * If a file is changed when it is not locked, optimistic locking is used.
 * So if there are no conflicts, synchronize will not complain
 * (this goes also for non-binary files that can be automatically merged).
 * If someone checked in changes that can not be merged with the local file,
 * a conflict will be reported.
 * If that happens, the user will have two files: the latest local file
 * and the latest from the repository.
 * <p>
 * To disable password caching use {@link #getClientConfiguration()}. Note that 
 * this changes the settings for all subversion clients in the current user account.
 *
 * @todo how to handle hasLocalChanges and missing files; autodelete? revert?
 * @todo report new files or folders in any subfolder as localChanges? currently does not.
 * @todo recursive lock?
 * 
 * @author Staffan Olsson (solsson)
 * @version $Id$
 * @see SimpleWorkingCopy for a more automated client
 * @see ReposWorkingCopy for documentation of the standard methods
 */
public class ManagedWorkingCopy implements ReposWorkingCopy {

	final Logger logger = LoggerFactory.getLogger(this.getClass());	
	
	ReposWorkingCopy workingCopy;
	
	ConfigureClient configureClient = new DefaultReposClientSettings();
	
	File workingCopyFolder;
	
	public ManagedWorkingCopy(CheckoutSettings settings) {
		workingCopy = ReposWorkingCopyFactory.getClient(settings);
		
		workingCopyFolder = settings.getWorkingCopyFolder();
	}

	public void add(File path) {
		workingCopy.add(path);
	}

	/**
	 * Adds all unversioned contents to version control.
	 */
	public void addNew() {
		workingCopy.addNew(workingCopyFolder);
	}
	
	public void addNew(File path) {
		workingCopy.addNew(path);
	}

	public void addNotifyListener(NotifyListener notifyListener) {
		workingCopy.addNotifyListener(notifyListener);
	}

	/**
	 * Note that checkout will work as an update if the working copy is already checked out.
	 * 
	 * Each checkout enforces the {@link DefaultReposClientSettings}.
	 */
	public void checkout() throws RepositoryAccessException {
		configureClient.update(workingCopy.getClientConfiguration());
		workingCopy.checkout();
	}

	/**
	 * Full commit of the working copy.
	 */
	public void commit(String commitMessage) throws ConflictException, RepositoryAccessException {
		commit(workingCopyFolder, commitMessage);
	}
	
	public void commit(File path, String commitMessage) throws ConflictException, RepositoryAccessException {
		workingCopy.commit(path, commitMessage);
	}

	public void delete(File path) {
		// temporarily revert the file so it can be deleted by the client
		if (!path.exists()) {
			logger.debug("The file or folder is gone already, marking for delete anyway");
			// svn clients handle this gracefully, they mark the file for deletion even if it is gone
		}
		workingCopy.delete(path);
	}

	public boolean hasLocalChanges(File path) {
		return workingCopy.hasLocalChanges(path);
	}

	public boolean isVersioned(File path) {
		try {
			return workingCopy.isVersioned(path);
		} catch (WorkingCopyAccessException e) {
			// client lib throws client exception if the parent path is not versioned
			try {
				if (!isVersioned(path.getParentFile())) return false;
			} catch (Throwable t) {
			}
			throw e;
		}
	}

	public void lock(File path, String lockMessage) {
		workingCopy.lock(path, lockMessage);
	}

	/**
	 * Supports moving files that have already moved, but not folders.
	 */
	public void move(File from, File to) {
		if (!from.exists() && workingCopy.isVersioned(from)) {
			logger.info("Detected an attempt to move file that has already been moved. Restoring the source temporarily.");
			if (!to.exists()) throw new IllegalArgumentException("Neither source nor destination for move exists");
			if (to.isFile() && workingCopy.isVersioned(to)) throw new IllegalArgumentException("The destination file for move is already versioned: " + to);
			// TODO test that, if it is a folder, it has svn metadata from the old location
			if (to.renameTo(from)) {
				this.copy(from, to);
			} else {
				throw new WorkingCopyAccessException("Could not restore the moved folder " + from + " from the destination " + to + ", so the versioned move can not be done");
			}
		} else {
			this.copy(from, to);
		}
		pathDeleteRecursive(from);
		this.delete(from);
	}
	
	
	public void copy(File from, File to) {
		workingCopy.copy(from, to);
	}

	public void revert(File path) {
		workingCopy.revert(path);
	}

	public void update(File path) throws ConflictException, RepositoryAccessException {
		workingCopy.update(path);
	}

	public boolean hasLocalChanges() {
		return workingCopy.hasLocalChanges();
	}

	public void markConflictResolved(ConflictInformation conflictInformation) {
		workingCopy.markConflictResolved(conflictInformation);
	}

	public void synchronize(String commitMessage) throws ConflictException, RepositoryAccessException {
		workingCopy.synchronize(commitMessage);
	}

	public void update() throws ConflictException, RepositoryAccessException {
		workingCopy.update();
	}

	public void unlock(File path) {
		workingCopy.unlock(path);
	}

	public boolean isAdministrativeFolder(File path) {
		return workingCopy.isAdministrativeFolder(path);
	}

	public boolean isIgnore(File path) {
		return workingCopy.isIgnore(path);
	}

	public ClientConfiguration getClientConfiguration() {
		return workingCopy.getClientConfiguration();
	}

	public VersionedProperties getProperties(File path) {
		return workingCopy.getProperties(path);
	}

	public VersionedFileProperties getPropertiesForFile(File file) {
		return workingCopy.getPropertiesForFile(file);
	}

	public VersionedFolderProperties getPropertiesForFolder(File folder) {
		return workingCopy.getPropertiesForFolder(folder);
	}

	public File[] getUnversionedContents(File path) {
		return workingCopy.getUnversionedContents(path);
	}

	private boolean pathDeleteRecursive(File remove) {
		if (!remove.getAbsolutePath().contains(workingCopyFolder.getAbsolutePath())) {
			throw new RuntimeException("Can not delete folder outside the working copy: " + remove);
		}
		if (remove.exists()) {
			File[] files = remove.listFiles();
			for (int i = 0; i < files.length; i++) {
				if (files[i].isDirectory()) {
					pathDeleteRecursive(files[i]);
				} else {
					files[i].delete();
				}
			}
		}
		return (remove.delete());
	}

	/**
	 * For testing
	 * @return 
	 */
	ReposWorkingCopy getWorkingCopy() {
		return workingCopy;
	}
}
