<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<%@ page contentType="text/html; charset=UTF-8" language="java" errorPage="" %>
<%@ page pageEncoding="UTF-8" %>
<%@ page import="java.io.*" %>
<%@ page import="java.util.*" %>
<%

final String LOGFILE = "WEB-INF/log/repos.log";  // relative to servlet context
final String EXTERNAL_LOGFILE = "C:/srv/log/repos.log"; // tried if the first is not found
final String START_OF_LOG_ENTRY = "200"; // start of new log-entry. to distinguish log entry from exception line
final byte POSITION_LEVEL = 24; // first letter in log-level. to determine severity

// get referrer page
Enumeration en;
en = request.getHeaderNames();
String refText = null;

while (en.hasMoreElements())
{
    String key = (String)en.nextElement();
    if (key.equals("referer"))
    {
        refText = request.getHeader(key);
        break;
    }
}

// get file handle
File file = null;
try {
	file = new File(pageContext.getServletContext().getRealPath(LOGFILE));
	if (!file.exists() || !file.canRead() || file.length()==0 )
	    file = new File(EXTERNAL_LOGFILE);
	if (!file.exists())
		response.sendError(response.SC_NO_CONTENT, "Logfile not found, tried\n" + LOGFILE + " (relative to webapp) and \n" + EXTERNAL_LOGFILE );
} catch (Exception ex) {
	response.sendError(response.SC_NO_CONTENT, ex.getMessage() );
}

// process Clear submit
String clear = pageContext.getRequest().getParameter("clear");
if( clear!=null && clear.compareTo("Clear")==0 ) {
	try {
	   FileWriter fw = new FileWriter(file);
	   fw.write("");
	   fw.close();
	   response.sendRedirect("index.jsp");
	} catch (Exception ex) {
	   response.sendError(response.SC_NO_CONTENT, ex.getMessage() );
	}
}
%>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Log4j log</title>
<html:base/>
</head>
<link rel="stylesheet" type="text/css" href="../css/main.css">
<body>
<table width="960" cellpadding="1" border="0" cellspacing="0" id="contents">
<tr><td>&nbsp;</td><td><p>Log file reader by optime.se reading <code><%= file.getPath() %></code></p></td>
<tr><td>&nbsp;</td><td><a name="top"><a href="<% out.print(refText); %>">Go back</a> &nbsp; <a href="#bottom">newest entries</a></td></tr>
</tr>
<%
String currentLine = "";

try {
   FileReader fileReader = new FileReader(file);
   BufferedReader bufferedReader = new BufferedReader(fileReader);
   int l = 0;
   char c = '0'; // default red
   while( (currentLine = bufferedReader.readLine()) != null ){
   		// color selection
		if (currentLine.length() > POSITION_LEVEL && currentLine.charAt(POSITION_LEVEL-1)==' ') {
			switch ((int)currentLine.charAt(POSITION_LEVEL)) {
				case (int)'D': c = '0'; break;
				case (int)'I': c = '4'; break;
				case (int)'W': c = '8'; break;
				case (int)'E': c = 'B'; break;
				case (int)'F': c = 'F'; break;
			}
		}
   		// print
   		out.print(
   		new StringBuffer()
		.append("<tr><td bgcolor=\"#").append(c).append(c).append("6666\"/>&nbsp;&nbsp;</td>")
		.append("<td nowrap bgcolor=\"#")
   		.append( currentLine.startsWith(START_OF_LOG_ENTRY) && l++%2==0 ? "DDDDDD" : "EEEEEE" )
   		.append( "\"><small>" )
   		.append( currentLine.startsWith(START_OF_LOG_ENTRY) ? currentLine : " &nbsp; &nbsp; &nbsp; " + currentLine )
   		.append( "</small></td></tr>" )
   		);
   }
   currentLine=null;
   bufferedReader.close();
   fileReader.close();
   file = null;
} catch (Exception e) {
   %><tr><td>&nbsp;</td><td bgcolor="#DDDDDD">Error: <%= e.getMessage() %></td></tr><%
}
%>
<tr>
<td bgcolor="#CCCCCC">&nbsp;</td>
<td bgcolor="#CCCCCC" valign="middle"><form name="editlog" id="form1" method="GET" action=""><input name="clear" type="submit" id="clear" value="Clear"/></form></td>
</tr>
<tr><td>&nbsp;</td><td><a name="bottom"><a href="<% out.print (refText); %>">Go back</a>  &nbsp; <a href="#top">top</a> &nbsp; JSP log file reader by Staffan Olsson</td></tr>
</table>
</body>
</html>
