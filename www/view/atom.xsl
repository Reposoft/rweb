<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:atom="http://www.w3.org/2005/Atom">
	
  	<xsl:output method="html"/>
  	
	<xsl:param name="contentsOnly"></xsl:param>
	
	<xsl:param name="theme">/repos</xsl:param><!-- default theme, need to import conf to get theme -->
  	
	<xsl:template match="/">
		<xsl:if test="not($contentsOnly)">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>
					<xsl:text>repos.se: </xsl:text>
					<xsl:value-of select="/atom:feed/atom:title"/>
				</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<link rel="stylesheet" type="text/css" href="{$theme}/style/global.css"/>
			</head>
			<body class="messages">
				<xsl:apply-templates select="*"/>
			</body>
		</html>
		</xsl:if>
		<xsl:if test="$contentsOnly">
			<xsl:apply-templates select="*"/>
		</xsl:if>
	</xsl:template>
	<!-- renders the HTML body contents, all with one common root div -->
	<xsl:template match="/atom:feed">
		<div class="info"><!-- sould be class: frame -->
			<div>
				<h1>
					<xsl:value-of select="atom:author/atom:name"/>
					<xsl:value-of select="' '"/>
					<xsl:value-of select="atom:title"/>
				</h1>
				<p>
					<xsl:value-of select="atom:subtitle"/>
				</p>
				<ul id="headlines">
					<xsl:apply-templates select="atom:entry" mode="headline"/>
				</ul>
			</div>
			<xsl:apply-templates select="atom:entry">
				<xsl:sort select="atom:updated" order="descending"/>
			</xsl:apply-templates>
		</div>
	</xsl:template>
	<xsl:template match="atom:entry" mode="headline">
		<li>
			<a href="#{atom:updated}">
				<xsl:value-of select="atom:title"/>
			</a>
			<xsl:value-of select="' '"/>
			<span class="datetime">
				<xsl:value-of select="atom:updated"/>
			</span>
		</li>
	</xsl:template>
	<xsl:template match="atom:entry">
		<div id="{atom:updated}">
			<h2>
				<xsl:value-of select="atom:title"/>
			</h2>
			<div>
				<p>
					<!-- need to use the newline template -->
					<xsl:value-of select="atom:summary"/>
				</p>
				<p>
				<span class="username">
					<xsl:value-of select="atom:author/atom:name"/>
				</span>
				<xsl:value-of select="' '"/>
				<span class="datetime">
					<xsl:value-of select="atom:updated"/>
				</span>
				<xsl:value-of select="' '"/>
				<span class="url">
					<xsl:value-of select="atom:link/@href"/>
				</span>
				</p>
			</div>
			<a name="{atom:updated}"/>
		</div>
	</xsl:template>

</xsl:stylesheet>
