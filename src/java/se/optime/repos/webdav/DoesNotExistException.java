/*
 * Created on 2004-okt-06
 */
package se.optime.repos.webdav;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.RepositoryPath;

/**
 * Connection was successful but the resource is not there.
 * @author solsson
 * @version $Id$
 */
public class DoesNotExistException extends RepositoryAccessException {

    /**
     * @param path
     */
    public DoesNotExistException(RepositoryPath path) {
        super(RESOURCE_DOES_NOT_EXIST, path);
    }

}
