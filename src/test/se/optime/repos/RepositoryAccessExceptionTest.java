/*
 * Created on 2004-okt-09
 */
package se.optime.repos;

import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryAccessExceptionTest extends TestCase {

    public void testInstaintiate() throws Exception {
        int error = 999;
        StubRepositoryPath path = new StubRepositoryPath();
        Exception ex = new Exception("test exeption");
        RepositoryAccessException e = new TheSubclass(error,path,ex);
        assertEquals("error",error,e.getError());
        assertEquals("path",path,e.getPath());
        assertEquals("cause",ex,e.getCause());
    }
    
    /**
     * Concrete subclass
     * @author solsson
     * @version $Id$
     */
    private class TheSubclass extends RepositoryAccessException {
        /**
         * @param error
         * @param path
         * @param cause
         */
        public TheSubclass(int error, RepositoryPath path, Throwable cause) {
            super(error, path, cause);
        }
    }
    
}
