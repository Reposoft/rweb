/*
 * Created on 2004-okt-05
 */
package se.optime.repos;

/**
 * Base class for exceptions when accessing web resources.
 * 
 * <p>Runtime exceptions are supposedly handled at request dispatcher level.
 * However code at any level might catch this exception if it can recover from it,
 * or if it needs customized error handling. This exception being thrown implies that
 * the operation failed and nothing has changed in the repository</p>
 * 
 * <p>Note that when throwing a repository exception, no custom message is written.
 * This is becuase these messages are rarely understandable for the user. Instead, declare
 * a new error code, with internally understandable name, and provide a real debug
 * message using standard logging. Then rest assured that the web framework will take
 * good care of the user.</p>
 * 
 * <p>Concrete subclasses of this base exception are encouraged to provide constructors
 * that are understandable to the thrower, with no need for knowing about the error codes.</p>
 * 
 * @author solsson
 * @version $Id$
 */
public abstract class RepositoryAccessException extends RuntimeException {
    
    // collect error codes here 
    public static final int UNKNOWN_ERROR = 0;
    public static final int UNKNOWN_ACCESS_ERROR = 1;
    public static final int UNKNOWN_READ_ERROR = 2;
    public static final int UNKNOWN_WRITE_ERROR = 3;
    public static final int FILE_EXPECTED = 10;
    public static final int DIRECTORY_EXPECTED = 11;
    public static final int RESULTING_URL_INVALID = 12;
    public static final int EXPLICIT_URL_INVALID = 13;
    public static final int ACCESS_FAILED_TEMPORARILY = 14;
    public static final int RESOURCE_DOES_NOT_EXIST = 15;
    public static final int WRONG_FILETYPE = 16;
    public static final int EMPTY_CONTENTS = 17;
    public static final int CANNOT_PARSE_CONTENTS = 18;
    
    // also allow HTTP status codes
    
    private int error;
    private RepositoryPath path = null;
       
    public RepositoryAccessException(int error) {
        super();
        this.error = error;
    }

    public RepositoryAccessException(int error, RepositoryPath path) {
        super();
        this.error = error;
        setPath(path);
    }

    public RepositoryAccessException(int error, RepositoryPath path, Throwable cause) {
        super(cause);
        this.error = error;
        setPath(path);
    }
    
    public RepositoryAccessException(int error, Throwable cause) {
        super(cause);
        this.error = error;
    } 
    
    /**
     * Produce a message understandable for the user
     * @return Localised readable error message
     * @see java.lang.Throwable#getLocalizedMessage()
     */
    public String getLocalizedMessage() {
        // @param error Code named by iternal constant
        // @param path The path for which the error occured, may be null
        // TODO
        return super.getLocalizedMessage();
    }
    /* (non-Javadoc)
     * @see java.lang.Throwable#getMessage()
     */
    public String getMessage() {
        // TODO
        return super.getMessage();
    }
/**
     * @return Returns the path.
     */
    public RepositoryPath getPath() {
        return path;
    }
    /**
     * @param path The path to set.
     */
    public void setPath(RepositoryPath path) {
        this.path = path;
    }
    /**
     * @return Returns the error.
     */
    public int getError() {
        return error;
    }
}
