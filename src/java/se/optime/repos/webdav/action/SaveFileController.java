/*
 * Created on 2004-okt-05
 */
package se.optime.repos.webdav.action;

import javax.servlet.http.HttpServletRequest;

import org.springframework.web.servlet.View;

/**
 * @author solsson
 * @version $Id$
 */
public class SaveFileController extends RepositoryActionController {
    
    // idea on how to add custom validation and formattion
    private Object contentsPostProcessor;

    /* (non-Javadoc)
     * @see se.optime.repos.webdav.action.RepositoryActionController#execute(javax.servlet.http.HttpServletRequest, se.optime.repos.webdav.action.RepositoryUpdate)
     */
    protected View execute(HttpServletRequest request, RepositoryUpdate resource) throws Exception {
        resource.commitContents();
        return resource.getForwardView();
    }


}
