/*
 * Created on 2004-okt-10
 */
package se.optime.repos.webdav;

import java.io.IOException;

import org.apache.commons.httpclient.HttpException;

import se.optime.repos.StubRepositoryPath;

import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class ConnectionExceptionTest extends TestCase {

    /*
     * Class under test for void ConnectionException(boolean, RepositoryPath, HttpException)
     */
    public void testConnectionException_HttpException() {
        HttpException e = new HttpException();
        e.setReasonCode(-999);
        ConnectionException ce = new ConnectionException(false,new StubRepositoryPath().setTestValues(),e);
        assertEquals("same error code",-999,ce.getError());
    }

    /*
     * Class under test for void ConnectionException(boolean, RepositoryPath, IOException)
     */
    public void testConnectionException_IOException() {
        IOException e = new IOException();
        ConnectionException ce = new ConnectionException(false,new StubRepositoryPath().setTestValues(),e);
        assertEquals("read error",ConnectionException.UNKNOWN_READ_ERROR,ce.getError());
    }
    
    public void testConnectionException_IOException_write() {
        IOException e = new IOException();
        ConnectionException ce = new ConnectionException(true,new StubRepositoryPath().setTestValues(),e);
        assertEquals("write error",ConnectionException.UNKNOWN_WRITE_ERROR,ce.getError());
    }    

}
