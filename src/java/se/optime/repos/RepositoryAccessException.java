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
    
    // related to contents of accessed resource
    public static final int EMPTY_CONTENTS = 717;
    public static final int CANNOT_PARSE_CONTENTS = 718;
    
    // connection related
    public static final int RESOURCE_ACCESS_ERROR = 901;
    public static final int RESOURCE_READ_ERROR = 902;
    public static final int RESOURCE_WRITE_ERROR = 903;
    public static final int RESOURCE_DOES_NOT_EXIST = 915; // internal version of HTTP_NOT_FOUND, should maybe have the same code
    
    // input related
    public static final int FILE_EXPECTED = 810;
    public static final int DIRECTORY_EXPECTED = 811;
    public static final int RESULTING_URL_INVALID = 812;
    public static final int EXPLICIT_URL_INVALID = 813;
    public static final int WRONG_FILETYPE = 916;
   
    // recognised and handled HttpClient codes
    
    // recognised and handled HTTP status codes
    public static final int HTTP_NOT_WEBDAV = 405;
    public static final int HTTP_NOT_FOUND = 404;
    public static final int HTTP_ACCESS_DENIED = 401;
    
    /**
     * Error code, determined by subclass
     */
    private int error;
    /**
     * The affected path
     */
    private RepositoryPath path = null;
    
    public RepositoryAccessException(int error) {
        super();
        this.error = error;
    }

    public RepositoryAccessException(int error, RepositoryPath path) {
        this(error);
        setPath(path);
    }

    public RepositoryAccessException(int error, RepositoryPath path, Throwable cause) {
        this(error,cause);
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
     * @todo implement error code to message mapping
     */
    public String getLocalizedMessage() {
        // @param error Code named by iternal constant
        // @param path The path for which the error occured, may be null
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
