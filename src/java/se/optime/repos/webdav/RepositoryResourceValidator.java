/*
 * Created on 2004-okt-02
 */
package se.optime.repos.webdav;

import org.springframework.validation.Errors;
import org.springframework.validation.Validator;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryResourceValidator implements Validator {

    /* (non-Javadoc)
     * @see org.springframework.validation.Validator#supports(java.lang.Class)
     */
    public boolean supports(Class clazz) {
        return clazz.isAssignableFrom(RepositoryResource.class);        
    }

    /* (non-Javadoc)
     * @see org.springframework.validation.Validator#validate(java.lang.Object, org.springframework.validation.Errors)
     */
    public void validate(Object command, Errors errors) {
        RepositoryResource resource = (RepositoryResource)command;
        String path = resource.getAbsolutePath();
        if (path==null)
            errors.reject("missingResourceUrl","Resource URL not specified");
        try {
            resource.getHttpURL();
        } catch (RuntimeException e) {
            errors.reject("invalidResourceUrl",e.getMessage());
        }
    }

}
