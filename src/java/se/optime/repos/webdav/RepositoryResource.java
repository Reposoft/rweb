/*
 * Created on 2004-okt-02
 */
package se.optime.repos.webdav;

import java.io.IOException;
import java.io.InputStream;
import java.io.Reader;
import java.net.MalformedURLException;
import java.net.URL;

import org.apache.commons.httpclient.HttpException;
import org.apache.commons.httpclient.HttpURL;
import org.apache.commons.httpclient.HttpsURL;
import org.apache.commons.httpclient.URIException;
import org.apache.webdav.lib.WebdavResource;
import org.springframework.core.io.AbstractResource;

import se.optime.repos.WebResource;
import se.optime.repos.user.StaticAuthenticationResolver;

/**
 * @author solsson
 * @version $Id$
 */
public class RepositoryResource extends AbstractResource
		implements WebResource {

    private String host = null;
    private int port = -1;
    private String repo = null;
    private String path = null;
    private String href = null;
    boolean secure = false;
    
    private String user = StaticAuthenticationResolver.getAuthenticatedUsername();
    private String pass = StaticAuthenticationResolver.getAuthenticatedPassword();
    
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
    
    /**
     * @return Reference to the resource
     */
    protected WebdavResource getDavFile() {
        HttpURL url = getHttpURL();
        try {
            return WebdavFactory.getWebdav().getWebdavResource(url);
        } catch (HttpException e) {
            throw new ConnectionException(false,this,e);
        } catch (IOException e) {
            throw new ConnectionException(false,this,e);
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
     * @see se.optime.repos.RepositoryPath#getRepo()
     */
    public String getRepo() {
        return repo;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.RepositoryPath#getPath()
     */
    public String getPath() {
        return path;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.RepositoryPath#getHref()
     */
    public String getHref() {
        return href;
    }

    protected HttpURL getHttpURL() {
        try {
            if (isSecure())
                return new HttpsURL(getUser(),getPass(),getHost(),getPort(),getAbsolutePath());
            else
                return new HttpURL(getUser(),getPass(),getHost(),getPort(),getAbsolutePath());
        } catch (URIException e) {
            throw new InvalidPathException(this,e);
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
        if (port<0)
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
    /**
     * @param host The host to set.
     */
    public void setHost(String host) {
        this.host = host;
    }
    /**
     * @param pass User's password
     */
    public void setPass(String pass) {
        this.pass = pass;
    }
    /**
     * @param secure Set to true if this is a secure connection.
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
    /**
     * @return True if this is a file resource
     */
    public boolean isFile() {
        return !isDirectory();
    }
    /**
     * @return True if the path represents a repository directory
     */
    public boolean isDirectory() {
        return getDavFile().isCollection();
    }
    /* (non-Javadoc)
     * @see se.optime.repos.WebResource#getReader()
     */
    public Reader getReader() {
        throw new UnsupportedOperationException("Method RepositoryResource#getReader not implemented yet.");
    }
    /* (non-Javadoc)
     * @see se.optime.repos.RepositoryPath#isSecure()
     */
    public boolean isSecure() {
        return secure;
    }
    /* (non-Javadoc)
     * @see se.optime.repos.RepositoryPath#getURL()
     */
    public URL getURL() {
        try {
            return new URL(getHttpURL().toString());
        } catch (MalformedURLException e) {
            throw new InvalidPathException(this,e);
        }
    }
    /* (non-Javadoc)
     * @see se.optime.repos.RepositoryPath#getIdentifierQuery()
     */
    public String getIdentifierQuery() {
        StringBuffer q = new StringBuffer();
        q.append("host=").append(getHost())
        	.append("&repo=").append(getRepo())
        	.append("&path=").append(getPath())
        	.append("&href=").append(getHref());
        if (port>=0)
            q.append("&port=").append(getPort());
        return q.toString();
    }
    /* (non-Javadoc)
     * @see org.springframework.core.io.Resource#exists()
     */
    public boolean exists() {
        return getDavFile().exists();
    }
}
