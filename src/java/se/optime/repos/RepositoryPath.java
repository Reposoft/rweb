/*
 * Created on Sep 8, 2004
 */
package se.optime.repos;

/**
 * Uniquely identifies a WebDAV repository resource.
 * 
 * <p>Syntactic rules (specified at setters below) are enforced by validators.</p>
 * 
 * @author solsson
 * @version $Id$
 * @see se.optime.repos.webdav.RepositoryPathValidator
 */
public interface RepositoryPath {
    
    /**
     * The extra filename extension appended to all requests that should be processed by this system
     */
    public static final String INCOMING_EXTENSION = ".jwa";
    
    /**
     * @return hostname/address for repository
     */
    public String getHost();
    
    /**
     * @return port at the host
     */
    public int getPort();
    
    /**
     * @return repository root path, starting slash marking host root, without tailing slash 
     */
    public String getRepo();
    
    /**
     * @return in-repository path, starting slash marking repository root. Add tailing slash if specifying file.
     */
    public String getPath();
    
    /**
     * @return resource href from path ( = filename)
     */
    public String getHref();
    
    /**
     * @return true if this resource is reached using a secure connection
     */
    public boolean isSecure();
    
    /**
     * @return resulting url
     */
    public java.net.URL getURL();
    
    /**
     * @return query string that fully identifies this resource. No ampersand at beginning or end, but always at least one parameter.
     */
    public String getIdentifierQuery();

}
