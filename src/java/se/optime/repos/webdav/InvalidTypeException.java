/*
 * Created on 2004-okt-05
 */
package se.optime.repos.webdav;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.RepositoryPath;

/**
 * Connection not attempted because path indicates wrong resource type.
 * 
 * <p>Distinct cases: <br />
 * a) {@link #InvalidTypeException(RepositoryPath) Wrong filesystem node type}<br />
 * b) {@link #InvalidTypeException(String,RepositoryPath) Wrong filename extension}
 * </p>
 * 
 * @author solsson
 * @version $Id$
 */
public class InvalidTypeException extends RepositoryAccessException {

    private String expectedFiletype = null;
    
    /**
     * Expected file but resource is directory, or the reverse.
     * 
     * <p>Path with <code>{@link RepositoryPath#getHref() href}==null</code> indicates directory</p>
     * 
     * @param path The unaccepted resource
     */
    public InvalidTypeException(RepositoryPath path) {
        super(getErrorCode(path), path);
    }

    /**
     * Expected a certain filetype, and file extension of this resource says it is not that type.
     * @param expectedFiletype Specifying the filetype, without a leading dot
     * @param path The unaccepted resource
     */
    public InvalidTypeException(String expectedFiletype, RepositoryPath path) {
        super(WRONG_FILETYPE, path);
        setExpectedFiletype(expectedFiletype);
    }
    
    /**
     * @param path href==null means directory
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
