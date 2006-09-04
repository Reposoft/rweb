<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">
	
  	<xsl:output method="html"/>
  	
		<xsl:param name="theme">/repos</xsl:param><!-- default theme, need to import conf to get theme -->
  	
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>
					<xsl:text>repos.se: </xsl:text>
					<xsl:value-of select="/atom:feed/atom:title"/>
				</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<link rel="stylesheet" type="text/css" href="/repos/style/common.css"/>
			</head>
			<body class="messages">
				<xsl:apply-templates select="*"/>
			</body>
		</html>
	</xsl:template>
	<xsl:template match="/atom:feed">
		<div class="frame">
			<div>
				<h1>
					<xsl:value-of select="atom:title"/>
				</h1>
				<p>
					<xsl:value-of select="atom:subtitle"/>
				</p>
			</div>
			<xsl:apply-templates select="atom:entry"/>
		</div>
	</xsl:template>
	<xsl:template match="atom:entry">
		<div id="{atom:updated}">
			<h2>
				<a name="{atom:updated}">
					<xsl:value-of select="atom:title"/>
				</a>
			</h2>
			<div>
				<p>
					<xsl:value-of select="atom:summary"/>
				</p>
				<span class="user">
					username
				</span>
				<span class="datetime">
					<xsl:value-of select="atom:updated"/>
				</span>
				<span>
					<xsl:value-of select="atom:link/@href"/>
				</span>
			</div>
		</div>
	</xsl:template>

</xsl:stylesheet>
