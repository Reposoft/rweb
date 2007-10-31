<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
	<xsl:apply-templates select="*"/>
</xsl:template>

<xsl:template match="testpage">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<title>repos: prepare script</title>
<link href="../../style/global.css" rel="stylesheet" type="text/css" />
<link href="../../style/docs.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="ReposPrepare.js"></script>
<!-- can not use unittest.js, because it uses code from prepare -->
<script type="text/javascript" src="../unittest.js"></script>

</head>
<body>
<h1>Repos script prepare</h1>
<p>Repos prepare is a script that runs at include-time and sets up common functinality
needed <em>before</em> jquery and other standard libs are loaded.</p>
<p>Shared functionality that is not needed first of all is placed in the Repos class
and can benefit from jquery.</p>

<h2>unit tests</h2>
<p>Contrary to other tests, this test does not use ../unittest.js, because unittest.js uses code from this prepare script.</p>
<p>Note that ecmaunit won't be able to create the result div unless Prepare does it's job (so there's a unit test).</p>
<div id="testlog"></div>
<script type="text/javascript">

function TestPrepare() {
    
    // created element has no properties if created without namespace in namespace-aware browser	
	this.testCreateElement = function() {
		var e = document.createElement();
		this.assertTrue(typeof(e) != undefined);
		e.id = 'newelement';
		this.assertEquals('newelement', e.id);
		document.getElementsByTagName('body')[0].appendChild(e);
		this.assertEquals(e, document.getElementById('newelement'));
	};
	
}
testrun(TestPrepare);

</script>

</body>
</html>

</xsl:template>

</xsl:stylesheet>
