/*
 * Created on 2004-okt-10
 */
package se.optime.repos.webdav;

import se.optime.repos.RepositoryAccessException;
import se.optime.repos.StubRepositoryPath;
import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class DoesNotExistExceptionTest extends TestCase {

    public void testDoesNotExistException() {
        assertEquals("RESOURCE_DOES_NOT_EXIST",RepositoryAccessException.RESOURCE_DOES_NOT_EXIST,
                new DoesNotExistException(new StubRepositoryPath().setTestValues()).getError());
    }

}
