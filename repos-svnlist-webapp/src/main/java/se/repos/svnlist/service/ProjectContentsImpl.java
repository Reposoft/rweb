/* Copyright 2006 Optime data Sweden
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package se.repos.svnlist.service;

import java.util.ArrayList;
import java.util.Collection;
import java.util.Iterator;

import org.tmatesoft.svn.core.SVNDirEntry;
import org.tmatesoft.svn.core.SVNException;
import org.tmatesoft.svn.core.SVNNodeKind;
import org.tmatesoft.svn.core.SVNURL;
import org.tmatesoft.svn.core.auth.ISVNAuthenticationManager;
import org.tmatesoft.svn.core.internal.io.dav.DAVRepositoryFactory;
import org.tmatesoft.svn.core.internal.io.svn.SVNRepositoryFactoryImpl;
import org.tmatesoft.svn.core.io.SVNRepository;
import org.tmatesoft.svn.core.io.SVNRepositoryFactory;
import org.tmatesoft.svn.core.wc.SVNWCUtil;

import se.repos.svnlist.service.files.RepositoryEntry;
import se.repos.svnlist.service.files.SVNDirectory;
import se.repos.svnlist.service.files.SVNFile;
import se.repos.validation.strings.RejectStringEndNotAllowed;
import se.repos.validation.strings.RejectStringIsEmpty;

public class ProjectContentsImpl implements ProjectContents {

    /*
     * default values:
     */
    String url = "http://svn.collab.net/repos/svn/";
    String name = "anonymous";
    String password = "anonymous";
	
	public String getUrl() {
		return url;
	}
	
	private SVNRepository getRepository() {
        /*
         * initializes the library (it must be done before ever using the
         * library itself)
         */
        setupLibrary();
        new RejectStringIsEmpty().validate(url);
        
        SVNRepository repository = null;
        try {
            /*
             * Creates an instance of SVNRepository to work with the repository.
             * All user's requests to the repository are relative to the
             * repository location used to create this SVNRepository.
             * SVNURL is a wrapper for URL strings that refer to repository locations.
             */
            repository = SVNRepositoryFactory.create(SVNURL.parseURIEncoded(url));
        } catch (SVNException svne) {
            /*
             * Perhaps a malformed URL is the cause of this exception
             */
            System.err
                    .println("error while creating an SVNRepository for location '"
                            + url + "': " + svne.getMessage());
            System.exit(1);
        }
 
        /*
         * User's authentication information is provided via an ISVNAuthenticationManager
         * instance. SVNWCUtil creates a default usre's authentication manager given user's
         * name and password.
         */
        ISVNAuthenticationManager authManager = SVNWCUtil.createDefaultAuthenticationManager(name, password);
 
        /*
         * Sets the manager of the user's authentication information that will 
         * be used to authenticate the user to the server (if needed) during 
         * operations handled by the SVNRepository.
         */
        repository.setAuthenticationManager(authManager);
        
        /*
         * Gets the latest revision number of the repository
         */
        long latestRevision = -1;
        try {
            latestRevision = repository.getLatestRevision();
        } catch (SVNException svne) {
            System.err
                    .println("error while fetching the latest repository revision: "
                            + svne.getMessage());
            System.exit(1);
        }
        System.out.println("");
        System.out.println("---------------------------------------------");
        System.out.println("Repository latest revision: " + latestRevision);
        
        return repository;
	}

	private void printTheList(SVNRepository repository) {
		try {
            /*
             * Checks up if the specified path/to/repository part of the URL
             * really corresponds to a directory. If doesn't the program exits.
             * SVNNodeKind is that one who says what is located at a path in a
             * revision. -1 means the latest revision.
             */
            SVNNodeKind nodeKind = repository.checkPath("", -1);
            if (nodeKind == SVNNodeKind.NONE) {
                System.err.println("There is no entry at '" + url + "'.");
                System.exit(1);
            } else if (nodeKind == SVNNodeKind.FILE) {
                System.err.println("The entry at '" + url + "' is a file while a directory was expected.");
                System.exit(1);
            }
            /*
             * getRepositoryRoot returns the actual root directory where the
             * repository was created
             */
            System.out.println("Repository Root: " + repository.getRepositoryRoot(true));
            /*
             * getRepositoryUUID returns Universal Unique IDentifier (UUID) - an
             * identifier of the repository
             */
            System.out.println("Repository UUID: " + repository.getRepositoryUUID(true));
            System.out.println("");
 
            /*
             * Displays the repository tree at the current path - "" (what means
             * the path/to/repository directory)
             */
            listEntries(repository, "");
        } catch (SVNException svne) {
            System.err.println("error while listing entries: "
                    + svne.getMessage());
            System.exit(1);
        }
	}

    /*
     * Initializes the library to work with a repository either via svn:// 
     * (and svn+ssh://) or via http:// (and https://)
     */
    private static void setupLibrary() {
        /*
         * for DAV (over http and https)
         */
        DAVRepositoryFactory.setup();
 
        /*
         * for SVN (over svn and svn+ssh)
         */
        SVNRepositoryFactoryImpl.setup();
    }
    
    /*
     * Called recursively to obtain all entries that make up the repository tree
     * repository - an SVNRepository which interface is used to carry out the
     * request, in this case it's a request to get all entries in the directory
     * located at the path parameter;
     * 
     * path is a directory path relative to the repository location path (that
     * is a part of the URL used to create an SVNRepository instance);
     *  
     */
    private static Collection<RepositoryEntry> listEntries(SVNRepository repository, String path)
            throws SVNException {
        /*
         * Gets the contents of the directory specified by path at the latest
         * revision (for this purpose -1 is used here as the revision number to
         * mean HEAD-revision) getDir returns a Collection of SVNDirEntry
         * elements. SVNDirEntry represents information about the directory
         * entry. Here this information is used to get the entry name, the name
         * of the person who last changed this entry, the number of the revision
         * when it was last changed and the entry type to determine whether it's
         * a directory or a file. If it's a directory listEntries steps into a
         * next recursion to display the contents of this directory. The third
         * parameter of getDir is null and means that a user is not interested
         * in directory properties. The fourth one is null, too - the user
         * doesn't provide its own Collection instance and uses the one returned
         * by getDir.
         */
        Collection entries = repository.getDir(path, -1, null,
                (Collection) null);
        Collection<RepositoryEntry> target = new ArrayList<RepositoryEntry>(entries.size());
        Iterator iterator = entries.iterator();
        while (iterator.hasNext()) {
            SVNDirEntry entry = (SVNDirEntry) iterator.next();
            if (entry.getKind() == SVNNodeKind.DIR) {
                target.add(new SVNDirectory(entry));
            } else if (entry.getKind() == SVNNodeKind.FILE) {
            	target.add(new SVNFile(entry));
            }
        }
        return target;
    }

	public void setUrl(String absoluteUrl) {
		// todo add filename to constructor
		new RejectStringEndNotAllowed("/").validate(absoluteUrl);
		this.url = absoluteUrl;
	}

	public Collection<RepositoryEntry> getList(String path) {
		SVNRepository repository = getRepository();
		try {
			return listEntries(repository, "/");
		} catch (SVNException e) {
			// TODO auto-generated
			throw new RuntimeException("SVNException handling missing", e);
		}
	}

	public Collection<RepositoryEntry> getList() {
		return getList("/");
	}    
}
