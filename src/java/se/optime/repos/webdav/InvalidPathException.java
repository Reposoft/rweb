/*
 * Created on 2004-okt-05
 */
package se.optime.repos.webdav;

import java.net.MalformedURLException;

import org.apache.commons.httpclient.URIException;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.RepositoryPath;

/**
 * @author solsson
 * @version $Id$
 */
public class InvalidPathException extends RepositoryAccessException {

    /**
     * @param path
     * @param e
     */
    public InvalidPathException(RepositoryPath path, URIException e) {
        super(RESULTING_URL_INVALID,path,e);
        // TODO Auto-generated constructor stub
    }

    /**
     * @param path
     * @param e
     */
    public InvalidPathException(RepositoryPath path, MalformedURLException e) {
        super(RESULTING_URL_INVALID,path,e);        
        // TODO Auto-generated constructor stub
    }

}
