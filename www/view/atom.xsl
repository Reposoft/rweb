<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:atom="http://www.w3.org/2005/Atom">
	
	<!-- This stylesheet can be imported from any atom feed file.
	Browsers like Safari and Firefox2 will use their internal viewer. -->
	
	<xsl:output method="html" encoding="UTF-8" omit-xml-declaration="no" indent="no"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
  	
  	<!-- set to non-empty to transform only contensts of body tag -->
	<xsl:param name="contentsOnly"></xsl:param>

	<!-- static contents urls, set to /themes/any/?u= for automatic theme selection -->
	<xsl:param name="static" select="'/repos/'"/>
	<xsl:param name="cssUrl"><xsl:value-of select="$static"/>style/</xsl:param>
	  	
	<xsl:template match="/">
		<xsl:if test="not($contentsOnly)">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>
					<xsl:text>repos.se: </xsl:text>
					<xsl:value-of select="/atom:feed/atom:title"/>
				</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<!-- default stylesheet -->
				<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}global.css"/>
				<!-- install the repos script bundle -->
				<script type="text/javascript" src="{$static}scripts/head.js"></script>
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
		<div class="contents">
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
				<xsl:value-of select="atom:published | atom:updated"/>
			</span>
		</li>
	</xsl:template>
	<xsl:template match="atom:entry">
		<div id="{atom:updated}">
			<h2>
				<a name="{atom:updated}"></a>
				<xsl:value-of select="atom:title"/>
			</h2>
			<div>
				<xsl:if test="atom:link/@href">
					<p>
						<a href="{atom:link/@href}">
							<xsl:value-of select="atom:summary"/><!--  might need to use the newline template -->
						</a>
					</p>
				</xsl:if>
				<xsl:copy-of select="atom:content"/>
				<p>
				<span class="username">
					<xsl:value-of select="atom:author/atom:name"/>
				</span>
				<xsl:value-of select="' '"/>
				<span class="datetime">
					<xsl:value-of select="atom:published | atom:updated"/>
				</span>
				</p>
			</div>
		</div>
	</xsl:template>

</xsl:stylesheet>
