/*
 * Created on 2004-okt-01
 */
package se.optime.repos.webdav;

import java.io.InputStreamReader;
import java.io.PrintStream;
import java.text.SimpleDateFormat;
import java.util.Date;

import org.apache.commons.httpclient.HttpURL;
import org.apache.webdav.lib.WebdavResource;

import junit.framework.TestCase;

/**
 * Just to try the library
 * @author solsson
 */
public class SlideWebdavClientTest extends TestCase {

	PrintStream out = System.out;
	String HOST = "10.20.1.130";
	String FILE = "/repos/sbp/sbp/trunk/Kalle/Kalle.txt"; // "/repos/olsson/db_mysql1/olsson.sql";
	String USER = "a400335";
	String PASS = "guran";
	
	public void testGetFile() throws Exception {
		HttpURL url = new HttpURL(USER,PASS,HOST,HttpURL.DEFAULT_PORT,FILE);
		WebdavResource res = new WebdavResource(url);
		out.println("[test-debug] " + res.toString() + " exists? " + res.exists());
		InputStreamReader in = new InputStreamReader(res.getMethodData());
		char[] contents = new char[100];
		in.read(contents);
		StringBuffer newVersion = new StringBuffer().append(contents);
		newVersion.append('\n').append(new SimpleDateFormat().format(new Date()));
		res.putMethod(newVersion.toString());
	}
	
	public void testGetDirectory() throws Exception {
		
	}
}
