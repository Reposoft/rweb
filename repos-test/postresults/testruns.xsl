<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="/">
<html>
	<head>
		<title>Test results on this host</title>
		<link href="testrun.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
	<xsl:apply-templates select="testruns"/>	
	</body>
</html>
</xsl:template>

<xsl:template match="testruns">
	<xsl:apply-templates select="suite"/>
</xsl:template>

<xsl:template match="suite">
	<h2><xsl:value-of select="@id"/></h2>
	<xsl:apply-templates select="run"/>
</xsl:template>

<xsl:template match="run">
	<h3><a href="{@id}.html"><xsl:value-of select="@id"/></a></h3>
	<xsl:apply-templates select="*"/>
</xsl:template>

<xsl:template match="//run/*">
<div>
	<span class="label"><xsl:value-of select="name()"/>:</span>
	<xsl:value-of select="."/>
</div>
</xsl:template>

</xsl:stylesheet>
