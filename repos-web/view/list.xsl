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
	
	<xsl:param name="recursiveparam">
		<xsl:if test="/lists/@recursive">
			<xsl:text>&#38;recursive=1</xsl:text>
		</xsl:if>
	</xsl:param>
	
	<xsl:template match="/">
	<html>
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
			<meta name="repos-repository" content="{/lists/@repo}" />
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
	</html>
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
			<xsl:choose>
				<xsl:when test="/lists/@rev">
					<xsl:if test="../@parent">
						<a id="parent" class="command translate" href="?target={../@parent}{$baseparam}&amp;rev={/lists/@rev}{$recursiveparam}">up</a>
					</xsl:if>
					<a id="list" href="?target={$target}{$baseparam}{$recursiveparam}">current version</a>
					<a id="history" href="../log/?target={$target}&amp;rev={../@rev}{$baseparam}">history up to version <xsl:value-of select="../@rev"/></a>
					<xsl:if test="/lists/@recursive">
						<a id="listshort" class="command translate" href="{$web}open/list/?target={$target}&amp;rev={../@rev}{$baseparam}">flat</a>
					</xsl:if>
					<xsl:if test="not(/lists/@recursive)">
						<a id="listrecursive" class="command translate" href="{$web}open/list/?target={$target}&amp;rev={../@rev}{$baseparam}&amp;recursive=1">recursive</a>
					</xsl:if>
				</xsl:when>
				<xsl:otherwise>
					<xsl:if test="../@parent">
						<a id="parent" class="command translate" href="?target={../@parent}{$baseparam}{$recursiveparam}">up</a>
					</xsl:if>
					<!-- Note that this does not forward the 'recursive' parameter.
						Log is always recursive, but its links to folders are for non-recursive navigation -->
					<a id="history" href="../log/?target={$target}{$baseparam}">history</a>
					<xsl:if test="/lists/@recursive">
						<a id="listshort" class="command translate" href="{$web}open/list/?target={$target}{$baseparam}">flat</a>
					</xsl:if>
					<xsl:if test="not(/lists/@recursive)">
						<a id="listrecursive" class="command translate" href="{$web}open/list/?target={$target}{$baseparam}&amp;recursive=1">recursive</a>
					</xsl:if>
				</xsl:otherwise>				
			</xsl:choose>
		</div>
		<h2>
			<a class="folder" href="{@path}">
				<xsl:if test="string-length(../@name) = 0 and ../@base">
					<span id="base" class="repo"><xsl:value-of select="../@base"/></span>
				</xsl:if>
				<xsl:value-of select="../@name"/>
			</a>
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
					<td>lock</td>
				</tr>
			</thead>
			<xsl:apply-templates select="*">
				<xsl:with-param name="parent" select="$target"/>
				<xsl:sort select="not(/lists/@recursive) and @kind = 'file'"/>
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
		<xsl:param name="revparam">
			<xsl:if test="/lists/@rev">
				<xsl:value-of select="'&amp;rev='"/>
				<!-- preserve requested revision so that old revision tree can be navigated -->
				<xsl:value-of select="/lists/@rev"></xsl:value-of>
			</xsl:if>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<tr id="row:{$id}" class="row n{$n mod 4}">
			<td>
				<a id="open:{$id}" class="folder" href="?target={$target}{$baseparam}{$revparam}{$recursiveparam}">
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
			<td>&#160;</td>
			<td>&#160;</td>
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
		<xsl:param name="revparam">
			<xsl:if test="/lists/@rev">
				<xsl:value-of select="'&amp;rev='"/>
				<!-- for files we expect navigation to end at the details page, so we don't preserve requested revision -->
				<xsl:value-of select="commit/@revision"></xsl:value-of>
			</xsl:if>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<tr id="row:{$id}" class="n{$n mod 4}">
			<td>
				<a id="open:{$id}" class="file-{$filetype} file" href="../?target={$target}{$baseparam}{$revparam}">
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
			<td>
				<xsl:if test="lock">
					<xsl:attribute name="class">lock</xsl:attribute>
					<span class="username">
						<xsl:if test="lock/comment">
							<xsl:attribute name="title">
								<xsl:value-of select="lock/comment"/>
							</xsl:attribute>
						</xsl:if>
						<xsl:value-of select="lock/owner"/>
					</span>
					<!-- metadata useful in plugins, should not be displayed -->
					<span class="datetime" style="display:none">
						<xsl:value-of select="lock/created"></xsl:value-of>
					</span>
				</xsl:if>
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
