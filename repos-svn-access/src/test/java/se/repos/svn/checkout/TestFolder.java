/* $license_header$
 */
package se.repos.svn.checkout;

import java.io.File;
import java.io.FilenameFilter;
import java.io.IOException;

public abstract class TestFolder {

	public static final String FOLDER_PREFIX = "ReposWorkingCopyTest";
	
	public static File getNew() {
		try {
			File tmp = File.createTempFile(FOLDER_PREFIX, "dir");
			tmp.delete();
			tmp.mkdir();
			tmp.deleteOnExit();
			return tmp;
		} catch (IOException e) {
			throw new RuntimeException("Could not create temp folder. Test can not continue.", e);
		}
	}
	
	/**
	 * seems taht deleteOnExit does not work very well so the tests should do this cleanUp in tearDown
	 */
	public static void cleanUp() {
		File tmpFolder = getNew().getParentFile();
		FilenameFilter filenameFilter = new FilenameFilter() {
			public boolean accept(File dir, String name) { return name.startsWith(FOLDER_PREFIX); }
		};
		File[] tmpDirs = tmpFolder.listFiles(filenameFilter);
		for (int i = 0; i < tmpDirs.length; i++) {
			if(!deleteDirectory(tmpDirs[i])) System.out.println("Could not delete temp folder " + tmpDirs[i].getAbsolutePath());
		}
	}
	
	private static boolean deleteDirectory(File path) {
		if( !path.getAbsolutePath().contains(FOLDER_PREFIX)) {
			throw new RuntimeException("An attempt was made to delete a folder that is not tmp: " + path);
		}
	    if( path.exists() ) {
	      File[] files = path.listFiles();
	      for(int i=0; i<files.length; i++) {
	         if(files[i].isDirectory()) {
	           deleteDirectory(files[i]);
	         }
	         else {
	           files[i].delete();
	         }
	      }
	    }
	    return( path.delete() );
	}
	
}
