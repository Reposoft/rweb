/*
 * Created on 2004-okt-06
 */
package se.optime.repos.webdav;

import java.io.IOException;

import org.apache.commons.httpclient.HttpException;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.RepositoryPath;

/**
 * Wrapper to analyze common checked exceptions lite HttpException and IOException.
 * @author solsson
 * @version $Id$
 */
public class WrappedHttpException extends RepositoryAccessException {

    /**
     * @param triedToWrite true if the error was caused during an attempt to write to the path, false for access/read attempt 
     * @param path path for which the error occured
     * @param cause the wrapped exception
     */
    public WrappedHttpException(boolean triedToWrite, RepositoryPath path, HttpException cause) {
        super(getErrorCode(triedToWrite,path,cause), path, cause);
        // TODO Auto-generated constructor stub
    }


    public WrappedHttpException(boolean triedToWrite, RepositoryPath path, IOException cause) {
        super(getErrorCode(triedToWrite,path,cause), path, cause);
        // TODO Auto-generated constructor stub
    }
    
    private static int getErrorCode(boolean triedToWrite, RepositoryPath path, IOException cause) {
        if (triedToWrite)
            return UNKNOWN_WRITE_ERROR;
        return UNKNOWN_READ_ERROR;
    }
}
