/*
 * Created on 2004-okt-02
 */
package se.optime.repos.webdav;

import org.springframework.validation.Errors;
import org.springframework.validation.Validator;

import se.optime.repos.RepositoryPath;

/**
 * Syntactic validation of RepositoryPath according to rules documented there.
 * @author solsson
 * @version $Id$
 * @see se.optime.repos.RepositoryPath
 */
public class RepositoryPathValidator implements Validator {

    /* (non-Javadoc)
     * @see org.springframework.validation.Validator#supports(java.lang.Class)
     */
    public boolean supports(Class clazz) {
        return (RepositoryPath.class.isAssignableFrom(clazz));      
    }

    /* (non-Javadoc)
     * @see org.springframework.validation.Validator#validate(java.lang.Object, org.springframework.validation.Errors)
     */
    public void validate(Object command, Errors errors) {
        RepositoryPath path = (RepositoryPath)command;
        if (path.getHost()==null)
            errors.rejectValue("host","host.missing",null,"Host must be specified");
        if (path.getRepo()==null)
            errors.rejectValue("repo","repo.missing",null,"Repository root must be specified");
        if (!path.getRepo().startsWith("/"))
            errors.rejectValue("repo","repo.nostartslash",null,"Repository must be specified from root, and thus start with a slash");
        if (path.getRepo().endsWith("/"))
            errors.rejectValue("repo","repo.noendslash",null,"Repository can not end with slash");
        if (path.getPath()==null)
            errors.rejectValue("path","path.missing",null,"Path must be specified");
        if (!path.getPath().startsWith("/"))
            errors.rejectValue("path","path.nostartslash",null,"Path must be specified from root, and thus start with a slash");
        if (path.getHref()!=null) {
            if(!path.getPath().endsWith("/"))
                errors.rejectValue("path","path.noendslash",null,"Path must end with slash when file is specified");
            if(path.getHref().indexOf('/')>=0)
                errors.rejectValue("href","href.nested",null,"Href may not contain slash");
            if(path.getHref().indexOf('.')==0)
                errors.rejectValue("href","href.hidden",null,"Href specifies a hidden file");
            if(path.getHref().lastIndexOf('.')<=0)
                errors.rejectValue("href","href.noextension",null,"Filename must have an extension");
        }
    }

}
