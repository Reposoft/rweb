<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	
	<xsl:output method="html" encoding="UTF-8" omit-xml-declaration="no" indent="no"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>

	<xsl:param name="web">/repos/</xsl:param>

	<xsl:param name="cssUrl"><xsl:value-of select="$web"/>style/</xsl:param>
	<xsl:param name="cssUrl-pe"><xsl:value-of select="$web"/>themes/pe/style/</xsl:param>
	
	<xsl:param name="spacer" select="' &#160; '"/>
	
	<xsl:template match="/">
		<head>
			<title>
				<xsl:text>Repos: </xsl:text>
				<xsl:value-of select="'histroric folder'"/>
			</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<!-- if search crawlers has access, contents should not be cached -->
			<meta name="robots" content="noarchive"/>
			<link rel="shortcut icon" href="/favicon.ico"/>
			<!-- default stylesheets -->
			<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}global.css"/>
			<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}repository/repository.css"/>
			<!-- alternative stylesheets -->
			<link title="pe" rel="alternate stylesheet" type="text/css" href="{$cssUrl-pe}global.css"/>
			<link title="pe" rel="alternate stylesheet" type="text/css" href="{$cssUrl-pe}repository/repository.css"/>
			<!-- install the repos script bundle -->
			<script type="text/javascript" src="{$web}scripts/head.js"></script>
		</head>
		<body class="repository xml">
			<xsl:apply-templates select="*"/>
		</body>
	</xsl:template>
	
	<xsl:template match="lists">
		<xsl:apply-templates select="*"/>
	</xsl:template>
	
	<xsl:template match="list">
		<div id="commandbar">
			<a class="command" id="repository" href="{@path}">current version</a>
			<a class="command" id="history" href="../log/?target={../@target}&amp;rev={../@rev}">history for version <xsl:value-of select="../@rev"/></a>
			<a class="command" id="historycurrent" href="../log/?target={../@target}">history of current version</a>
		</div>
		<div id="contents">
			<h2>
				<span class="path">
					<xsl:call-template name="getBasename"/>
				</span>
				<xsl:if test="../@rev">
					<xsl:value-of select="$spacer"/>
					<span class="revision">
						<xsl:value-of select="../@rev"/>
					</span>
				</xsl:if>
			</h2>
			<xsl:apply-templates select="*"/>
		</div>
	</xsl:template>
	
	<xsl:template match="entry[@kind='dir']">
		<xsl:param name="id">
			<xsl:call-template name="getFileID"/>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<div id="row:{$id}" class="row n{$n mod 4}">
			<div class="actions">

			</div>
			<a id="f:{$id}" class="folder" href="../?target={../../@target}{name}&amp;rev={commit/@revision}">
				<xsl:value-of select="name"/>
			</a>
			<span class="revision">
				<xsl:value-of select="commit/@revision"/>
			</span>
			<span class="username">
				<xsl:value-of select="commit/author"/>
			</span>
			<span class="datetime">
				<xsl:value-of select="commit/date"/>
			</span>
		</div>
	</xsl:template>	
	
	<xsl:template match="entry[@kind='file']">
		<xsl:param name="filetype">
			<xsl:call-template name="getFiletype">
				<xsl:with-param name="filename" select="name"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="id">
			<xsl:call-template name="getFileID"/>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<div id="row:{$id}" class="row n{$n mod 4}">
			<div class="actions">

			</div>
			<a id="f:{$id}" class="file-{$filetype} file" href="../?target={../../@target}{name}&amp;rev={commit/@revision}">
				<xsl:value-of select="name"/>
			</a>
			<span class="revision">
				<xsl:value-of select="commit/@revision"/>
			</span>
			<span class="username">
				<xsl:value-of select="commit/author"/>
			</span>
			<span class="datetime">
				<xsl:value-of select="commit/date"/>
			</span>
			<span class="filesize">
				<xsl:value-of select="size"/>
			</span>
		</div>
	</xsl:template>

	<xsl:template name="getFiletype">
		<xsl:param name="filename" select="@href"/>
		<xsl:variable name="type" select="substring-after($filename,'.')"/>
		<xsl:choose>
			<xsl:when test="$type">
				<xsl:call-template name="getFiletype">
					<xsl:with-param name="filename" select="$type"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="lcletters">abcdefghijklmnopqrstuvwxyz</xsl:variable>
				<xsl:variable name="ucletters">ABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>
				<xsl:value-of select="translate($filename,$ucletters,$lcletters)"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="getFileID">
		<xsl:param name="filename" select="@href"/>
		<xsl:value-of select="translate($filename,'%/()@&amp;','______')"/>
	</xsl:template>
	
	<xsl:template name="getBasename">
		<xsl:param name="path" select="@path"/>
		<xsl:if test="not(contains($path,'/'))">
			<xsl:value-of select="$path"/>
		</xsl:if>
		<xsl:if test="contains($path,'/')">
			<xsl:call-template name="getBasename">
				<xsl:with-param name="path">
					<xsl:value-of select="substring-after($path,'/')"/>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
</xsl:stylesheet>
