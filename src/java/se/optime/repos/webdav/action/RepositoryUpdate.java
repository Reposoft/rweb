/*
 * Created on 2004-okt-05
 */
package se.optime.repos.webdav.action;

import java.io.IOException;

import org.apache.commons.httpclient.HttpException;
import org.springframework.core.io.Resource;
import org.springframework.web.servlet.View;
import org.springframework.web.servlet.view.RedirectView;

import se.optime.repos.webdav.InvalidContentsException;
import se.optime.repos.webdav.InvalidTypeException;
import se.optime.repos.webdav.RepositoryResource;
import se.optime.repos.webdav.ConnectionException;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryUpdate extends RepositoryResource {

    private boolean allowEmpty = false;
    private String forward = null;
    private String contents = null;

    /**
     * Save changes to this file, if there are any.
     * Empty contents will not be saves unless allowEmpty is set to true.
     */
    public void commitContents() {
        if (!allowEmpty && getContents()==null)
            throw new InvalidContentsException(this);
        try {
            getDavFile().putMethod(getContents());
        } catch (HttpException e) {
            throw new ConnectionException(true,this,e);
        } catch (IOException e) {
            throw new ConnectionException(true,this,e);
        }
    }

    /** 
     * @return MVC View that can be returned to the request handler
     */
    public View getForwardView() {
        return new RedirectView(getForward());
    }

    /**
     * @param allowEmpty Set to true to allow writing zero length contents to resource. Defaults to false.
     */
    public void setAllowEmpty(boolean allowEmpty) {
        this.allowEmpty = allowEmpty;
    }
    /**
     * @param contents The new contents to save
     */
    public void setContents(String contents) {
        this.contents = contents;
    }
    /**
     * @param forward The URL to forward to after action has been successfuly processed
     */
    public void setForward(String forward) {
        this.forward = forward;
    }
    /**
     * @return Returns the allowEmpty.
     */
    public boolean isAllowEmpty() {
        return allowEmpty;
    }
    /**
     * @return Returns the contents.
     */
    public String getContents() {
        return contents;
    }
    /**
     * @return Returns the forward.
     */
    public String getForward() {
        return forward;
    }
    /**
     * Create a subdirectory or a file inside this directory.
     * @see org.springframework.core.io.Resource#createRelative(java.lang.String)
     * @throws InvalidPathException if this resource does not allow nested contents (for example if it is not a directory)
     * @throws InvalidContentsException if relativePath is not a valid resource name
     */
    public Resource createRelative(String relativePath) throws IOException {
        if (isFile())
            throw new InvalidTypeException(this);
        throw new UnsupportedOperationException(
                "Method RepositoryUpdate#createRelative not implemented yet.");
    }
}
