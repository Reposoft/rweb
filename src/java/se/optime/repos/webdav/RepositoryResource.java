/*
 * Created on 2004-okt-02
 */
package se.optime.repos.webdav;

import java.io.IOException;
import java.io.InputStream;

import org.apache.commons.httpclient.HttpException;
import org.apache.commons.httpclient.HttpURL;
import org.apache.commons.httpclient.HttpsURL;
import org.apache.commons.httpclient.URIException;
import org.apache.webdav.lib.WebdavResource;
import org.springframework.core.io.AbstractResource;
import org.springframework.core.io.Resource;
import org.springframework.web.servlet.View;
import org.springframework.web.servlet.view.RedirectView;

import se.optime.repos.user.StaticAuthenticationResolver;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryResource extends AbstractResource
		implements RepositoryPath, Resource {

    private String host = null;
    private int port = 0;
    private String repo = null;
    private String path = null;
    private String href = null;
    boolean secure = false;
    
    private String user = StaticAuthenticationResolver.getAuthenticatedUsername();
    private String pass = StaticAuthenticationResolver.getAuthenticatedPassword();
    
    private String contents = null;
    
    /**
     * @param href The href to set.
     */
    public void setHref(String href) {
        this.href = href;
    }
    /**
     * @param path The path to set.
     */
    public void setPath(String path) {
        this.path = path;
    }
    /**
     * @param repo The repo to set.
     */
    public void setRepo(String repo) {
        this.repo = repo;
    }
    
    WebdavResource getDavFile() {
        HttpURL url = getHttpURL();
        try {
            return new WebdavResource(url);
        } catch (HttpException e) {
            throw new RuntimeException("Could not get " + url.toString(),e);
        } catch (IOException e) {
            throw new RuntimeException("Could not read " + url.toString(),e);
        }
    }
    
    /* (non-Javadoc)
     * @see org.springframework.core.io.Resource#getDescription()
     */
    public String getDescription() {
        return getDavFile().toString();
    }

    /* (non-Javadoc)
     * @see org.springframework.core.io.InputStreamSource#getInputStream()
     */
    public InputStream getInputStream() throws IOException {
        return getDavFile().getMethodData();
    }
    
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#getRepo()
     */
    public String getRepo() {
        return repo;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#getPath()
     */
    public String getPath() {
        return path;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#getHref()
     */
    public String getHref() {
        return href;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#getHttpURL()
     */
    public HttpURL getHttpURL() {
        try {
            if (isSecure())
                return new HttpsURL(getUser(),getPass(),getHost(),getPort(),getAbsolutePath());
            else
                return new HttpURL(getUser(),getPass(),getHost(),getPort(),getAbsolutePath());
        } catch (URIException e) {
            throw new RuntimeException("Invalid URL",e);
        }
    }
    
    public String getAbsolutePath() {
        return new StringBuffer()
        	.append(getRepo())
        	.append(getPath())
        	.append(getHref())
        	.toString();
    }
    
    /* (non-Javadoc)
     * @see org.springframework.core.io.Resource#getFilename()
     */
    public String getFilename() {
        return href;
    }

    /**
     * @return Returns the port.
     */
    public int getPort() {
        if (port==0)
            if (isSecure())
                return HttpsURL.DEFAULT_PORT;
            else
                return HttpURL.DEFAULT_PORT;
        return port;
    }
    /**
     * @param port The port to set.
     */
    public void setPort(int port) {
        this.port = port;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#getHost()
     */
    public String getHost() {
        return host;
    }
    /**
     * @return Returns the pass.
     */
    protected String getPass() {
        return pass;
    }
    /**
     * @return Returns the user.
     */
    protected String getUser() {
        return user;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#isSecure()
     */
    public boolean isSecure() {
        return secure;
    }
    /**
     * @param host The host to set.
     */
    public void setHost(String host) {
        this.host = host;
    }
    /**
     * @param pass The pass to set.
     */
    public void setPass(String pass) {
        this.pass = pass;
    }
    /**
     * @param secure The secure to set.
     */
    public void setSecure(boolean secure) {
        this.secure = secure;
    }
    /**
     * @param user The user to set.
     */
    public void setUser(String user) {
        this.user = user;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#getQuery()
     */
    public String getQuery() {
        StringBuffer q = new StringBuffer();
        q.append("host=").append(getHost())
        	.append("&repo=").append(getRepo())
        	.append("&path=").append(getPath())
        	.append("&href=").append(getHref());
        if (port!=0)
            q.append("&port=").append(getPort());
        return q.toString();
    }
    
    /**
     * @return Updated contents if anything has changed since the repository checkout
     */
    protected String getContents() {
        return contents;
    }
    /**
     * @param contents If changed from the version in repository, contents of the file is stored here
     */
    public void setContents(String contents) {
        this.contents = contents;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#commitChanges()
     */
    public void commitChanges() {
        if (getContents()==null)
            throw new RuntimeException("No changes stored");
        try {
            getDavFile().putMethod(getContents());
        } catch (HttpException e) {
            throw new RuntimeException("Could not save changes");
        } catch (IOException e) {
            throw new RuntimeException("Error writing contents");
        }
    }
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#isChanged()
     */
    public boolean isChanged() {
        return getContents()!=null;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.webdav.RepositoryPath#getRedirectTo()
     */
    public View getRedirectTo() {
        return new RedirectView(new StringBuffer()
                .append(getFilename()).append(RepositoryCommandController.DEFAULT_EXTENSION)
                .append('?').append(getQuery()).toString());
    }
}
