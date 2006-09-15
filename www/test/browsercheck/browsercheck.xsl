<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
	<xsl:apply-templates select="*"/>
</xsl:template>

<xsl:template match="testpage">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<title>repos.se browser check</title>
<link href="../../style/global.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">

function checkJavascript() {
	var e = document.getElementById('script');
	e.className = 'check_ok';
	e.innerHTML = 'Your browser supports javascript';
	window.setTimeout('checkCookie()', 500);
}

// there shoulc be one cookie 'repos_testcookie' set from the php page
function checkCookie() {
	var name = /repos_testcookie/;
	if (!document.cookie) return fail("Cookies not supported in the XML document");
	c = document.cookie;
	if (!name.test(c)) return fail("Your browser does not accept cookies from the server");
	ok("Your browser supports cookies");
	window.setTimeout('done()', 500);
}

function done() {
	var hr = _createElement('hr');
	_body().appendChild(hr);
	// TODO add summary
}

function fail(message) {
	return _result(message, 'check_failed');
}

function ok(message) {
	return _result(message, 'check_ok');
}

function _result(message, cssclass) {
	var d = _createElement('div');
	d.className = cssclass;
	d.innerHTML = message;
	_body().appendChild(d);
	return true;
}

function _body() {
	return document.getElementsByTagName('body')[0];
}

function _createElement(tagname) {
	if (document.createElementNS) {
		return document.createElementNS('http://www.w3.org/1999/xhtml', tagname);
	} else { // IE does not support createElementNS
		return document.createElement(tagname);	
	}
}

</script>
<style type="text/css">
div {
	margin: 5px;
	padding: 5px;
}
div.check_ok {
	background-color: #99CC99;
}
div.check_failed {
	background-color: #CC3333;
}
</style>
</head>
<body onload="checkJavascript()">
<h1>repos.se browser check</h1>
<p><a class="action" href="../../">return to startpage</a></p> 
<hr />
<div id="xslt" class="check_ok">Your browser supports XSLT transforms</div>
<div id="script" class="check_failed">Your browser does not support javascript</div>
</body>
</html>

</xsl:template>

</xsl:stylesheet>
