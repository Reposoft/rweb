<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ page session="true" %>
<%@ page errorPage="/exception.jsp" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="spring" uri="http://www.springframework.org/tags" %>
<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%-- 
Edit a document formatted as XHTML-strict
Needs the following model:
- contents: the document resource
--%>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><c:out value="<%= request.getAttribute("url") %>"/></title>
		<jsp:include page="../includes/head.inc.jsp"/>
    </head>
    <body>
		<jsp:include page="../includes/top.inc.jsp"/>
		<form action="save.jwa" method="POST">
		<textarea rows="40" cols="60" style="height:100%; width:100%;"><%= request.getAttribute("contents") %></textarea><br/>
		<input type="submit" value="Save"/>
		</form>
		<jsp:include page="../includes/end.inc.jsp"/>
		<% /* <div id="autosaveDiv">
			<iframe width="100%" height="20" src="autosave.jwa" style="position:absolute; left:0px; bottom:0px; width:100%; right:0px; background-color:#333333;"></iframe>
		</div> */ %>
    </body>
</html>