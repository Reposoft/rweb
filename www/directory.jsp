<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ page session="true" %>
<%@ page errorPage="/error.jsp" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="spring" uri="http://www.springframework.org/tags" %>
<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%@ taglib prefix="repos" uri="http://www.optime.se/repos/tags" %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>repos.se</title>
		<jsp:include page="repos.head.inc.jsp"/>
    </head>
    <body>
		<jsp:include page="repos.top.inc.jsp"/>
		<jsp:include page="repos.commandbar.inc.jsp"/>
		<h3>${resource.path}</h3>
		<p>Directories are currently not showed in Repos. Please go to <a href="${resource.URL}">the repository</a>.</p>
		<jsp:include page="repos.bottom.inc.jsp"/>
    </body>
</html>