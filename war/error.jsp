<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ page session="false" %>
<%@ page isErrorPage="true" %>
<%@ page import="java.io.*" %>
<%@ page import="org.apache.commons.logging.LogFactory" %>
<% LogFactory.getLog("exception.jsp").error("JSP error at " + request.getHeader("Referer"),exception); %> 
<html>
<head><title>error message</title>
<link href="../../css/main.css" rel="stylesheet" type="text/css">
</head>

<body topmargin="10" leftmargin="10" marginheight="10" marginwidth="10">

<p class="big">Page error</p>

<p>An unhandled error has occured<br/>
<i><%=exception.getMessage()%></i></p>

<p>Error details</p>
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