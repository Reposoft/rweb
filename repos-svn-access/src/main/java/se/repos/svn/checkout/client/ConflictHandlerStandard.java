/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;
import java.io.FilenameFilter;

import se.repos.svn.checkout.ConflictInformation;

/**
 * Leaves conflicting files as recommend by standard subversion clients.
 * 
 * <pre>
	file.txt (merged file with conflict markers, hard to understand for users)
	file.txt.mine (the latest local file)
	file.txt.r48 (the revision checked out before doing local changes)
	file.txt.r49 (the latest shared file)
 * </pre>
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ConflictHandlerStandard implements ConflictHandler {

	/**
	 * @throws IllegalArgumentException if not all the standard files exist
	 */
	public ConflictInformation handleConflictingFile(File path) {
		if (!path.isAbsolute()) throw new IllegalArgumentException("Conflict path " + path + " is not absolute");
		if (!path.exists()) throw new IllegalArgumentException("Conflict file " + path + " does not exist");
		if (!path.isFile()) throw new IllegalArgumentException("Conflict path " + path + " is not a directory");
		return new Names(path);
	}

	private class Names implements ConflictInformation {
		private File tagetPath;
		private File usedRepositoryFile;
		private File mergedFile;
		private File userFile;
		private File repositoryFile;
		
		Names(File path) {
			this.tagetPath = path;
			this.mergedFile = path;
			this.userFile = toUserFile(path);
			if (!userFile.exists()) throw new IllegalArgumentException("User file for conflict " + this.userFile + " does not exist");
			toRepositoryFiles(path);
		}
		
		// set usedRepositoryFile and repositoryFile
		private void toRepositoryFiles(File path) throws IllegalArgumentException {
			String filename = path.getName();
			File folder = path.getParentFile();
			final String namestart = filename + ".r";
			FilenameFilter filenameFilter = new FilenameFilter() {
				public boolean accept(File dir, String name) { return name.startsWith(namestart); }
			};
			File[] names = folder.listFiles(filenameFilter);
			if (names.length != 2) throw new IllegalArgumentException("Conflict should have two files matching '" + namestart + "*', found " + names.length);
			if (names[0].compareTo(names[1])<0) {
				this.usedRepositoryFile = names[0];
				this.repositoryFile = names[1];
			} else {
				this.usedRepositoryFile = names[1];
				this.repositoryFile = names[0];
			}
		}

		private File toUserFile(File path) {
			return new File(path.getAbsolutePath() + ".mine");
		}

		public File getRepositoryFile() {
			return this.repositoryFile;
		}

		public File getUserFile() {
			return this.userFile;
		}

		public File getMergedFile() {
			return this.mergedFile;
		}

		public File getUsedRepositoryFile() {
			return this.usedRepositoryFile;
		}

		public File getTargetPath() {
			return this.tagetPath;
		}
		
	}

	public void afterConflictResolved(ConflictInformation conflictInformation) {
		conflictInformation.getUserFile().delete();
		conflictInformation.getUsedRepositoryFile().delete();
		conflictInformation.getRepositoryFile().delete();
	}
	
}
