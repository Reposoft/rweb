/*
 * Created on 2004-okt-05
 */
package se.optime.repos.webdav.action;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.RepositoryPath;

/**
 * Not used yet. Just brainstorming.
 * @author solsson
 * @version $Id$
 */
public class LockingException extends RepositoryAccessException {

    /**
     * Used upon update attempt when discovered that the resource is already locked
     * @param path
     */
    public LockingException(RepositoryPath path) {
        super(0, path);
        // TODO Auto-generated constructor stub
    }
    
    /**
     * Used if locking attemt throws exception
     * @param path
     * @param cause
     */
    public LockingException(RepositoryPath path, Throwable cause) {
        super(0, path, cause);
//      TODO Auto-generated constructor stub
    }

    
}
