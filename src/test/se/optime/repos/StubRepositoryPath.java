/*
 * Created on 2004-okt-09
 */
package se.optime.repos;

import java.net.MalformedURLException;
import java.net.URL;

/**
 * Copletely separated getters and setters for all fields.
 * @author solsson
 * @version $Id$
 */
public class StubRepositoryPath implements RepositoryPath {

    private String host = null;
    private int port = -1;
    private String repo = null;
    private String path = null;
    private String href = null;
    boolean secure = false;
    private URL URL = null;
    private String identifierQuery = null;
    
    public StubRepositoryPath setTestValues() {
        setHost("www.repos.se");
        setPort(80);
        setRepo("/repos/ordbehandlare");
        setPath("/testpath/");
        setHref("testfile.test");
        setIdentifierQuery("host=www.repos.se&repo=/repos/ordbehandlare&path=/testpath/&href=testfile.test");
        try {
            setURL(new URL("http://www.repos.se/repos/ordbehandlare/testpath/testfile.test"));
        } catch (MalformedURLException e) {
            System.out.print("[test-error] could not create stub url");
        }
        return this;
    }
    
    /**
     * @return Returns the host.
     */
    public String getHost() {
        return host;
    }
    /**
     * @param host The host to set.
     */
    public void setHost(String host) {
        this.host = host;
    }
    /**
     * @return Returns the href.
     */
    public String getHref() {
        return href;
    }
    /**
     * @param href The href to set.
     */
    public void setHref(String href) {
        this.href = href;
    }
    /**
     * @return Returns the identifierQuery.
     */
    public String getIdentifierQuery() {
        return identifierQuery;
    }
    /**
     * @param identifierQuery The identifierQuery to set.
     */
    public void setIdentifierQuery(String identifierQuery) {
        this.identifierQuery = identifierQuery;
    }
    /**
     * @return Returns the path.
     */
    public String getPath() {
        return path;
    }
    /**
     * @param path The path to set.
     */
    public void setPath(String path) {
        this.path = path;
    }
    /**
     * @return Returns the port.
     */
    public int getPort() {
        return port;
    }
    /**
     * @param port The port to set.
     */
    public void setPort(int port) {
        this.port = port;
    }
    /**
     * @return Returns the repo.
     */
    public String getRepo() {
        return repo;
    }
    /**
     * @param repo The repo to set.
     */
    public void setRepo(String repo) {
        this.repo = repo;
    }
    /**
     * @return Returns the secure.
     */
    public boolean isSecure() {
        return secure;
    }
    /**
     * @param secure The secure to set.
     */
    public void setSecure(boolean secure) {
        this.secure = secure;
    }
    /**
     * @return Returns the uRL.
     */
    public URL getURL() {
        return URL;
    }
    /**
     * @param url The uRL to set.
     */
    public void setURL(URL url) {
        URL = url;
    }
}
