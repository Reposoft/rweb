<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ page session="true" %>
<%@ page errorPage="/error.jsp" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="spring" uri="http://www.springframework.org/tags" %>
<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%@ taglib prefix="repos" uri="http://www.optime.se/repos/tags" %>
<%-- 
Edit a document formatted as XHTML-strict
Needs the following model:
- resource: the document resource
--%>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Repos: ${resource.path}${resource.filename}</title>
		<jsp:include page="../includes/head.inc.jsp"/>
		<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
		<script language="javascript" type="text/javascript">
		   tinyMCE.init({
		      mode : "textareas"
		   });
		</script>
    </head>
    <body>
		<jsp:include page="../includes/top.inc.jsp"/>
		<form action="${resource.filename}.jwa?${resource.query}" method="POST">
		<textarea name="contents" rows="30" cols="60" style="height:100%; width:100%;"><repos:stream contents="${resource.inputStream}"/></textarea><br />
		<input type="submit" value="Save"/>
		</form>
		<jsp:include page="../includes/end.inc.jsp"/>
		<% /* <div id="autosaveDiv">
			<iframe width="100%" height="20" src="autosave.jwa" style="position:absolute; left:0px; bottom:0px; width:100%; right:0px; background-color:#333333;"></iframe>
		</div> */ %>
    </body>
</html>