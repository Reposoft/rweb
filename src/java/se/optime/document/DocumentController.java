/*
 * Created on 2004-okt-02
 */
package se.optime.document;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.web.servlet.ModelAndView;

import se.optime.repos.webdav.RepositoryCommandController;
import se.optime.repos.webdav.RepositoryPath;

/**
 * @author solsson
 * @version $Id$
 */
public class DocumentController extends RepositoryCommandController {

    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryCommandController#handle(javax.servlet.http.HttpServletRequest, javax.servlet.http.HttpServletResponse, se.optime.repos.webdav.RepositoryPath)
     */
    protected ModelAndView show(HttpServletRequest request,
            HttpServletResponse response, RepositoryPath resource)
    		throws Exception {
        return new ModelAndView("document/edit","resource",resource);
    }

    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryCommandController#save(javax.servlet.http.HttpServletRequest, se.optime.repos.webdav.RepositoryPath)
     */
    protected ModelAndView save(HttpServletRequest request, RepositoryPath resource) throws Exception {
        if (resource.isChanged()) {
            logger.info("Saving changes to " + resource.toString());
            resource.commitChanges();
        } else {
            if (logger.isDebugEnabled())
                logger.debug("No changes made to " + resource.toString());
        }
        return new ModelAndView(resource.getRedirectTo());
    }

}
