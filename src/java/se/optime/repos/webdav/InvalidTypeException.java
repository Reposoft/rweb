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
public class InvalidTypeException extends RepositoryAccessException {

    /**
     * @param filetypeMismatch If the error is due to an unexpected filetype, false if wron resource type
     * @param path
     */
    public InvalidTypeException(boolean filetypeMismatch, RepositoryPath path) {
        super(getErrorCode(filetypeMismatch, path), path);
    }

    private static int getErrorCode(boolean filetypeMismatch, RepositoryPath path) {
        if (filetypeMismatch)
            return CANNOT_PARSE_CONTENTS;
        if (path.getHref()==null)
            return FILE_EXPECTED;
        else
            return DIRECTORY_EXPECTED;
    }

}
