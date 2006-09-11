<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
	<xsl:apply-templates select="*"/>
</xsl:template>

<xsl:template match="testpage">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<title>head.js testcases</title>
<script type="text/javascript" src="../lib/scriptaculous/prototype.js"></script>
<script type="text/javascript" src="../lib/scriptaculous/unittest.js"></script>
<script type="text/javascript" src="../head.js"></script>
<link href="../../style/global.css" rel="stylesheet" type="text/css" />
</head>

<body>
<h1>Test head.js, the Repos class, in an XML page.</h1>

<div id="testlog"></div>

<script type="text/javascript">

new Test.Unit.Runner({

	testRequirePrototype: function() { with(this) {
		fail("The Prototype require does not work in this page, for some reason, so currently it is included as a script tag in head");
	}},
	
	testDefaultNamespace: function() { with(this) {
		var ns = Repos.defaultNamespace;
		assertEqual("http://www.w3.org/1999/xhtml", ns, "Namespace must be handled because this is an XML page");
	}},
	
	testCreateElement: function() { with(this) {
		var e = Repos.createElement('div');
		e.id = 'mydiv';
		document.getElementsByTagName('body')[0].appendChild(e);
		var e2 = $('mydiv');
		assertEqual(e, e2, 'Element attributes should be working. They are not set if node is created without namespace in Gecko+XML');
	}},
	
	testStandardCreateElement: function() { with(this) {
		var e = document.createElement('div');
		e.id = 'mydiv2';
		document.getElementsByTagName('body')[0].appendChild(e);
		var e2 = $('mydiv2');
		assertEqual(e, e2, 'Repos should override the default behaviour of document.createElement to get namespace support in libs not tested with xml');
	}},
	
	testDocumentBody: function() { with(this) {
		var b = document.getElementsByTagName('body')[0];
		assertEqual(b, document.body, "Repos should set 'document.body' because it is used in many scripts");
	}},

	// for copy-paste
	testTemplate: function() { with(this) {
	}},

	teardown: function() { with(this) {}}
}, { });

</script>
</body>
</html>

</xsl:template>

</xsl:stylesheet>
