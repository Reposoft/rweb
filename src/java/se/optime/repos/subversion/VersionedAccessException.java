/*
 * Created on 2004-okt-05
 */
package se.optime.repos.subversion;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.RepositoryPath;

/**
 * Not used yet. Just brainstorming.
 * @author solsson
 * @version $Id$
 */
public abstract class VersionedAccessException extends RepositoryAccessException {

    /**
     * @param error
     */
    public VersionedAccessException(int error) {
        super(error);
        // TODO Auto-generated constructor stub
    }
    /**
     * @param error
     * @param path
     */
    public VersionedAccessException(int error, RepositoryPath path) {
        super(error, path);
        // TODO Auto-generated constructor stub
    }
    /**
     * @param error
     * @param path
     * @param cause
     */
    public VersionedAccessException(int error, RepositoryPath path,
            Throwable cause) {
        super(error, path, cause);
        // TODO Auto-generated constructor stub
    }
    /**
     * @param error
     * @param cause
     */
    public VersionedAccessException(int error, Throwable cause) {
        super(error, cause);
        // TODO Auto-generated constructor stub
    }
}
