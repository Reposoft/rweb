/*
 * Created on 2004-okt-10
 */
package se.optime.repos.webdav;

import org.springframework.validation.BindException;

import se.optime.repos.StubRepositoryPath;
import se.optime.repos.webdav.action.RepositoryUpdate;
import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryPathValidatorTest extends TestCase {

    RepositoryPathValidator v = new RepositoryPathValidator();
    
    public void testSupports() {
        assertTrue(v.supports(RepositoryResource.class));
        assertTrue(v.supports(RepositoryUpdate.class));
        assertFalse(v.supports(Object.class));
    }

    public void testValidateEmpty() {
        StubRepositoryPath p = new StubRepositoryPath();
        BindException e = new BindException(p,"testpath");
        v.validate(p,e);
        assertTrue("No valid fields",e.hasFieldErrors("repo"));
        assertTrue("No valid fields",e.hasFieldErrors("path"));
    }
    
    public void testValidateValid() {
        StubRepositoryPath p = new StubRepositoryPath().setTestValues();
        BindException e = new BindException(p,"testpath");
        v.validate(p,e);
        assertFalse("Valid path",e.hasErrors());
    }
    
    public void testValidateDirectory() {
        StubRepositoryPath p = new StubRepositoryPath().setTestValues();
        // path without end slash
        p.setPath("/testdir");
        BindException e = new BindException(p,"testpath");
        v.validate(p,e);
        assertTrue("Filename set, path must end with slash",e.hasFieldErrors("path"));
        // but valid with no file
        p.setHref(null);
        e = new BindException(p,"testpath");
        v.validate(p,e);
        assertFalse("Valid path (without ending slash) because no filename set",e.hasErrors());
    }

}
