/*
 * Created on 2004-okt-05
 */
package se.optime.repos;

import java.io.Reader;

import org.springframework.core.io.Resource;

/**
 * Providing the contents of a WebDAV file.
 * 
 * <p>This is the {@link org.springframework.core.io.Resource Springframework Resource} interface
 * identified by a repository path. As a WebDAV resource, it is assumed to be
 * updateable. Implementations must provide methods for reading the file,
 * and may also provide methods for writing to the file.</p>
 * 
 * @author solsson
 * @version $Id$
 */
public interface WebResource
		extends RepositoryPath, Resource {
    
    /**
     * @return true if current contents differ from repository
    public boolean isChanged();
     */    
    
    /**
     * @return a character encoding aware Reader for the entire contents
     */
    public Reader getReader();

}