/*
 * Created on 2004-okt-06
 */
package se.optime.repos.webdav;

import java.io.IOException;

import org.apache.commons.httpclient.HttpException;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.RepositoryPath;

/**
 * Connection attempted, URL seemed valid but reading or writing failed.
 * 
 * <p>Analyzes common checked exceptions like HttpException and IOException.
 * Distinguishes between read and write operations to determine severity in 
 * for exampel transaction management.</p>
 * 
 * @author solsson
 * @version $Id$
 */
public class ConnectionException extends RepositoryAccessException {

    private boolean isHttpError = false;
    
    /**
     * Create exception with HTTP error code
     * @param triedToWrite true if the error was caused during an attempt to write to the path, false for access/read attempt 
     * @param path path for which the error occured
     * @param cause the wrapped exception
     */
    public ConnectionException(boolean triedToWrite, RepositoryPath path, HttpException cause) {
        super(cause.getReasonCode(), path, cause);
        isHttpError = true;
    }

    public ConnectionException(boolean triedToWrite, RepositoryPath path, IOException cause) {
        super(getErrorCode(triedToWrite), path, cause);
    }
    
    private static int getErrorCode(boolean triedToWrite) {
        if (triedToWrite)
            return UNKNOWN_WRITE_ERROR;
        return UNKNOWN_READ_ERROR;
    }
}
