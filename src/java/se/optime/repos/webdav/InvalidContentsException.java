/*
 * Created on 2004-okt-05
 */
package se.optime.repos.webdav;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.RepositoryPath;

/**
 * @author solsson
 * @version $Id$
 */
public class InvalidContentsException extends RepositoryAccessException {

    /**
     * @param path
     */
    public InvalidContentsException(RepositoryPath path) {
        super(EMPTY_CONTENTS, path);
        // TODO Auto-generated constructor stub
    }
    
    public InvalidContentsException(RepositoryPath path, Throwable parseError) {
        super(CANNOT_PARSE_CONTENTS, path, parseError);
        // TODO Auto-generated constructor stub
    }

}
