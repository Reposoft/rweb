<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:output method="html" encoding="UTF-8" omit-xml-declaration="no" indent="no"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
	<xsl:param name="title">repos: list </xsl:param>

	<xsl:param name="web">/repos-web/</xsl:param>

	<xsl:param name="cssUrl"><xsl:value-of select="$web"/>style/</xsl:param>

	<xsl:param name="spacer" select="'&#160; '"/>

	<!-- repository already has @base appended (because it comes from getRepository) but some links need it -->
	<xsl:param name="baseparam">
		<xsl:if test="/lists/@base">
			<xsl:text>&#38;base=</xsl:text>
			<xsl:value-of select="/lists/@base"/>
		</xsl:if>
	</xsl:param>
	<xsl:template match="/">
		<head>
			<title>
				<xsl:value-of select="$title"/>
				<xsl:value-of select="'detailed'"/>
			</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<!-- if search crawlers has access, contents should not be cached -->
			<meta name="robots" content="noarchive"/>
			<link rel="shortcut icon" href="/favicon.ico"/>
			<!-- repos metadata -->
			<meta name="repos-service" content="open/list/" />
			<meta name="repos-target" content="{/lists/@target}" />
			<meta name="repos-base" content="{/lists/@base}" />
			<!-- default stylesheets -->
			<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}global.css"/>
			<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}repository/repository.css"/>
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
		<xsl:param name="target">
			<xsl:call-template name="getHref">
				<xsl:with-param name="href" select="../@target"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="url" select="concat(../@repo,$target)"/>
		<div id="commandbar">
			<a id="repository" href="{$url}">return to repository</a>
			<xsl:if test="../@rev">
				<!-- Note that this does not forward the 'recursive' parameter.
					It is assumed that old versions are always listed non-recursively
					(rev is applied to subfolder links so it can still be navigated) -->
				<a id="list" href="?target={$target}{$baseparam}">current version</a>
			</xsl:if>
			<a id="history" href="../log/?target={$target}&amp;rev={../@rev}{$baseparam}">show history<xsl:if test="../@rev"> for version <xsl:value-of select="../@rev"/></xsl:if></a>
		</div>
		<h2>
			<a class="folder" href="{@path}"><xsl:value-of select="../@name"/></a>
			<xsl:if test="../@rev">
				<xsl:value-of select="$spacer"/>
				<span class="revision">
					<xsl:value-of select="../@rev"/>
				</span>
			</xsl:if>
		</h2>
		<table class="index list">
			<thead>
				<tr>
					<td>path</td>
					<td>revision</td>
					<td>author</td>
					<td>last&#160;edited</td>
					<td>size</td>
				</tr>
			</thead>
			<xsl:apply-templates select="*">
				<xsl:with-param name="parent" select="$target"/>
				<!--<xsl:sort select="@kind"/>-->
				<xsl:sort select="name"/>
			</xsl:apply-templates>
		</table>
		<div id="footer">
		</div>
	</xsl:template>

	<xsl:template match="entry[@kind='dir']">
		<xsl:param name="parent" select="../../@target"/>
		<xsl:param name="id">
			<xsl:call-template name="getFileID">
				<xsl:with-param name="filename" select="name"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="target">
			<xsl:value-of select="$parent"/>
			<xsl:call-template name="getHref">
				<xsl:with-param name="href" select="name"/>
			</xsl:call-template>
			<xsl:value-of select="'/'"/>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<tr id="row:{$id}" class="row n{$n mod 4}">
			<td>
				<!-- <a id="open:{$id}" class="folder" href="../?target={$target}&amp;rev={commit/@revision}{$baseparam}"> -->
				<!-- unlike files, folders are used for navigating the tree so we should preserve the explicit rev -->
				<a id="open:{$id}" class="folder" href="../?target={$target}{$baseparam}&amp;rev={/lists/@rev}">
					<xsl:value-of select="name"/>
				</a>
			</td>
			<td class="revision">
				<xsl:value-of select="commit/@revision"/>
			</td>
			<td class="username">
				<xsl:value-of select="commit/author"/>
			</td>
			<td class="datetime">
				<xsl:value-of select="commit/date"/>
			</td>
			<td>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="entry[@kind='file']">
		<xsl:param name="parent" select="../../@target"/>
		<xsl:param name="filetype">
			<xsl:call-template name="getFiletype">
				<xsl:with-param name="filename" select="name"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="id">
			<xsl:call-template name="getFileID">
				<xsl:with-param name="filename" select="name"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="target">
			<xsl:value-of select="$parent"/>
			<xsl:call-template name="getHref">
				<xsl:with-param name="href" select="name"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<tr id="row:{$id}" class="n{$n mod 4}">
			<td>
				<a id="open:{$id}" class="file-{$filetype} file" href="../?target={$target}&amp;rev={commit/@revision}{$baseparam}">
					<xsl:value-of select="name"/>
				</a>
			</td>
			<td class="revision">
				<xsl:value-of select="commit/@revision"/>
			</td>
			<td class="username">
				<xsl:value-of select="commit/author"/>
			</td>
			<td class="datetime">
				<xsl:value-of select="commit/date"/>
			</td>
			<td class="filesize">
				<xsl:value-of select="size"/>
			</td>
		</tr>
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
		<xsl:value-of select="translate($filename,'%/()@&amp;+=,~$! ','_____________')"/>
	</xsl:template>

	<xsl:template name="getHref">
		<xsl:param name="href" select="@href"/>
		<xsl:choose>
			<xsl:when test="contains($href, '+')">
				<xsl:call-template name="getHref">
					<xsl:with-param name="href" select="substring-before($href,'+')"/>
				</xsl:call-template>
				<xsl:value-of select="'%2B'"/>
				<xsl:call-template name="getHref">
					<xsl:with-param name="href" select="substring-after($href,'+')"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="contains($href, '&amp;')">
				<xsl:call-template name="getHref">
					<xsl:with-param name="href" select="substring-before($href,'&amp;')"/>
				</xsl:call-template>
				<xsl:value-of select="'%26'"/>
				<xsl:call-template name="getHref">
					<xsl:with-param name="href" select="substring-after($href,'&amp;')"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="contains($href, '#')">
				<xsl:call-template name="getHref">
					<xsl:with-param name="href" select="substring-before($href,'#')"/>
				</xsl:call-template>
				<xsl:value-of select="'%23'"/>
				<xsl:call-template name="getHref">
					<xsl:with-param name="href" select="substring-after($href,'#')"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="contains($href, '%')">
				<xsl:call-template name="getHref">
					<xsl:with-param name="href" select="substring-before($href,'%')"/>
				</xsl:call-template>
				<xsl:value-of select="'%25'"/>
				<xsl:call-template name="getHref">
					<xsl:with-param name="href" select="substring-after($href,'%')"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$href"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
