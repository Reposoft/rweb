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

    private String expectedFiletype = null;
    
    /**
     * Expected file but resource is directory, or the reverse
     * @param path The unaccepted resource
     */
    public InvalidTypeException(RepositoryPath path) {
        super(getErrorCode(path), path);
    }

    /**
     * Expected a certain filetype, and suspecting this is not of the correct type
     * @param expectedFiletype Specifying the filetype
     * @param path The unaccepted resource
     */
    public InvalidTypeException(String expectedFiletype, RepositoryPath path) {
        super(WRONG_FILETYPE, path);
        setExpectedFiletype(expectedFiletype);
    }
    
    /**
     * @param path
     * @return error code based on if resource is a file or a directory
     */
    private static int getErrorCode(RepositoryPath path) {
        if (path.getHref()==null)
            return FILE_EXPECTED;
        else
            return DIRECTORY_EXPECTED;
    }

    /**
     * @return Returns the expectedFiletype.
     */
    public String getExpectedFiletype() {
        return expectedFiletype;
    }
    /**
     * @param expectedFiletype The expectedFiletype to set.
     */
    public void setExpectedFiletype(String expectedFiletype) {
        this.expectedFiletype = expectedFiletype;
    }
}
