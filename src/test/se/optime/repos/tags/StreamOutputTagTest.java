/*
 * Created on 2004-okt-10
 */
package se.optime.repos.tags;

import java.io.ByteArrayInputStream;
import java.io.InputStream;
import java.io.StringWriter;
import java.io.Writer;

import junit.framework.TestCase;

/**
 * @author solsson
 * @version $Id$
 */
public class StreamOutputTagTest extends TestCase {

    StreamOutputTag tag = new StreamOutputTag();
    
    public void testDoPipe() throws Exception {
        InputStream from = new ByteArrayInputStream("Test\nедц".getBytes());
        Writer to = new StringWriter();
        to.write('a');
        tag.doPipe(from,to);
        to.write('z');
        assertEquals("Should be same contents","aTest\nедцz",to.toString());
    }
    
    public void testDoPipeEmpty() throws Exception  {
        InputStream from = new ByteArrayInputStream(new byte[0]);
        Writer to = new StringWriter();
        to.write('a');
        tag.doPipe(from,to);
        to.write('z');
        assertEquals("Should be same contents","az",to.toString());        
    }
    
    public void testDoPipeNull() throws Exception  {
        Writer to = new StringWriter();
        try {
            tag.doPipe(null,to);
            fail("exception should be thrown for null input");
        } catch (Exception e) {
            System.out.println("[test-debug] StreamOutputTag throws " + e.getClass().getName() + " on null input");
        }
        assertTrue("nothing should be written",to.toString().length()==0);
    }    

    public void testDoPipeDestinationNull() throws Exception  {
        InputStream from = new ByteArrayInputStream("Test\nедц".getBytes());
        try {
            tag.doPipe(from,null);
            fail("exception should be thrown for null output stream");
        } catch (Exception e) {
            System.out.println("[test-debug] StreamOutputTag throws " + e.getClass().getName() + " on null output");
        }       
    }
    
}
