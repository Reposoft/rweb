<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ page session="false" %>
<%@ page isErrorPage="true" %>
<%@ page import="java.io.*" %>
<%@ page import="org.apache.commons.logging.LogFactory" %>
<% LogFactory.getLog("error.jsp").error("JSP error at " + request.getHeader("Referer"),exception); %> 
<html>
<head><title>Unknown Repos error</title>
<link href="css/repos-standard.css" rel="stylesheet" type="text/css">
</head>

<body topmargin="10" leftmargin="10" marginheight="10" marginwidth="10">

<h2>Page error</h2>
<p>The Java code executing your request has produced an error. We have probably never run in to this problem before. There is no handling of it.</p>
<h3>Error message<h3/>
<p><i><%=exception.getMessage()%></i></p>
<h3>Error details</h3>
<pre><small>
<%
	out.println(Long.toString(System.currentTimeMillis()));
    ByteArrayOutputStream ostr = new ByteArrayOutputStream();
    exception.printStackTrace(new PrintStream(ostr));
    out.print(ostr);
%>
</small></pre>

</body>
</html>