/*
 * Created on 2004-okt-10
 */
package se.optime.repos;

import java.io.ByteArrayInputStream;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.Reader;
import java.io.StringReader;

import org.springframework.core.io.Resource;

/**
 * Copletely separated getters and setters for all fields.
 * @author solsson
 * @version $Id$
 */
public class StubWebResource extends StubRepositoryPath
		implements WebResource {

    private Reader reader = null;
    private boolean exists = true;
    private boolean open = false;
    private InputStream inputStream = null;
    private String description = null;
    
    public static final String CONTENTS = "Test resource\nWöök wöök";
    public static final String DESCRIPTION = "Test resource description";
    
    /* (non-Javadoc)
     * @see se.optime.repos.StubRepositoryPath#setTestValues()
     */
    public StubRepositoryPath setTestValues() {
        super.setTestValues();
        setReader(new StringReader(CONTENTS));
        setDescription(DESCRIPTION);
        setInputStream(new ByteArrayInputStream(CONTENTS.getBytes()));
        return this;
    }
    
    public String getDescription() {
        return description;
    }
    public void setDescription(String description) {
        this.description = description;
    }
    public boolean exists() {
        return exists;
    }
    public void setExists(boolean exists) {
        this.exists = exists;
    }
    public InputStream getInputStream() {
        return inputStream;
    }
    public void setInputStream(InputStream inputStream) {
        this.inputStream = inputStream;
    }
    public boolean isOpen() {
        return open;
    }
    public void setOpen(boolean open) {
        this.open = open;
    }
    public Reader getReader() {
        return reader;
    }
    public void setReader(Reader reader) {
        this.reader = reader;
    }
    public String getFilename() {
        return super.getHref();
    }
    
    /* (non-Javadoc)
     * @see org.springframework.core.io.Resource#getFile()
     */
    public File getFile() throws IOException {
        throw new UnsupportedOperationException(
                "Method StubWebResource#getFile not implemented yet.");
    }
    
    /* (non-Javadoc)
     * @see org.springframework.core.io.Resource#createRelative(java.lang.String)
     */
    public Resource createRelative(String relativePath) throws IOException {
        throw new UnsupportedOperationException(
                "Method StubWebResource#createRelative not implemented yet.");
    }
    
}
