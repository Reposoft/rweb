/*
 * Created on 2004-okt-05
 */
package se.optime.repos;

import java.io.Reader;

import org.springframework.core.io.Resource;

/**
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
     * @return a character encoding agnostic Reader of the entire contents
     */
    public Reader getReader();

}