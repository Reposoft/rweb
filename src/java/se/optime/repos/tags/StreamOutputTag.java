/*
 * Created on 2004-okt-02
 */
package se.optime.repos.tags;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.Reader;
import java.io.Writer;

import org.springframework.web.servlet.tags.RequestContextAwareTag;

/**
 * JSP tag to print out all contents of an InputStream to the response writer.
 * 
 * <p>The doStartTag method just forwards to {@link #doPipe(java.io.InputStream, java.io.Writer) doPipe}
 * where all logic is done. Unit testing doPipe should be sufficient.</p>
 * 
 * @author solsson
 * @version $Id$
 */
public class StreamOutputTag extends RequestContextAwareTag {
    
    private InputStream contents = null;
    
    /* (non-Javadoc)
     * @see org.springframework.web.servlet.tags.RequestContextAwareTag#doStartTagInternal()
     */
    protected int doStartTagInternal() throws Exception {
        doPipe(contents,pageContext.getOut());
        return EVAL_BODY_INCLUDE;
    }
    
    protected void doPipe(InputStream from, Writer to) throws IOException {
        Reader reader = new BufferedReader(new InputStreamReader(from));
        int c;
        while ((c=reader.read()) != -1)
            to.write(c);
        reader.close();
        to.flush();
    }

    /**
     * @param contentStream The contentStream to set.
     */
    public void setContents(InputStream contentStream) {
        this.contents = contentStream;
    }
}
