<?xml version="1.0"?>
<!--
  ==== repos.se: Subversion directory listing layout ====
  To be set as SVNIndexXSLT in repository conf.
  Used at all directory levels, so urls must be absolute.
  (c) Staffan Olsson

  Note that browser transformations only work if the
  stylesheet is read from the same domain as the XML
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<!-- start transform -->
	<xsl:output method="xml" indent="no"/>
	<!-- wrapping the config parameter with a different name, to be able to set it in a transformet -->
	<xsl:param name="web">/repos</xsl:param>
	<!-- static contents urls, set to {$web}/style/ for default theme -->
	<xsl:param name="cssUrl"><xsl:value-of select="$web"/>/style/</xsl:param>
	<!-- start url for simple WebDAV-like manipulation of repository, empty if not available -->
	<xsl:param name="editUrl"><xsl:value-of select="$web"/>/edit</xsl:param>
	<!-- when spacer space can't be avoided -->
	<xsl:param name="spacer" select="' &#160; '"/>
	<!-- should the 'up' button be visible when the current folder is 'trunk' -->
	<xsl:param name="disable-up-at-trunk">yes</xsl:param>
	<!-- document skeleton -->
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>
					<xsl:text>repos.se </xsl:text>
					<xsl:value-of select="index/@path"/>
				</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<!-- if search crawlers has access, contents should not be cached -->
				<meta name="robots" content="noarchive"/>
				<!-- default stylesheets -->
				<link rel="stylesheet" type="text/css" href="{$cssUrl}global.css"/>
				<link rel="stylesheet" type="text/css" href="{$cssUrl}repository/repository.css"/>
				<link rel="shortcut icon" href="/favicon.ico"/>
				<!-- install the repos script bundle -->
				<script type="text/javascript" src="{$web}/scripts/head.js"></script>
			</head>
			<body class="repository xml">
				<xsl:apply-templates select="svn"/>
			</body>
		</html>
	</xsl:template>
	<xsl:template match="svn">
		<xsl:apply-templates select="index"/>
	</xsl:template>
	<!-- body contents -->
	<xsl:template match="index">
		<div class="workspace">
			<xsl:call-template name="commandbar"/>
			<xsl:call-template name="contents"/>
			<xsl:call-template name="footer"/>
		</div>
	</xsl:template>
	<!-- toolbar, directory actions -->
	<xsl:template name="commandbar">
		<xsl:param name="disable-up">
			<xsl:choose>
				<xsl:when test="'set to no to always enable up'='no'">no</xsl:when>
				<xsl:when test="contains(/svn/index/@path,'/trunk') and substring-after(/svn/index/@path, '/trunk')=''">yes</xsl:when>
				<xsl:otherwise>no</xsl:otherwise>
			</xsl:choose>
		</xsl:param>
		<div class="commandbar">
		<a id="reposbutton">
			<img src="{$web}/style/logo/repos1.png" border="0" align="right" width="72" height="18" alt="repos.se" title="Using repos.se stylesheet $Rev$"/>
		</a>
		<xsl:if test="/svn/index/updir">
			<xsl:if test="not($disable-up='yes')">
				<a id="parent" class="command translate" href="../">up</a>
			</xsl:if>
			<xsl:if test="$disable-up='yes'">
				<span id="parent" class="command translate">up</span>
			</xsl:if>
		</xsl:if>
		<xsl:if test="$editUrl">
			<a id="createfolder" class="command translate" href="{$editUrl}/mkdir/?path={@path}">new folder</a>
			<a id="upload" class="command translate" href="{$web}/upload/?path={@path}">upload</a>
		</xsl:if>
		<!--
		<a class="command" href="{$web}/tutorials/?show=networkfolder">
			<xsl:call-template name="showbutton">
				<xsl:with-param name="filetype" select="'_windowsfolder'"/>
			</xsl:call-template>open folder</a>
		<a class="command" href="{$web}/tutorials/?show=checkout">
			<xsl:call-template name="showbutton">
				<xsl:with-param name="filetype" select="'_tortoisefolder'"/>
			</xsl:call-template>check out</a>
		-->
		<a id="history" class="command translate" href="{$web}/open/log/?path={@path}">folder history</a>
		<a id="refresh" class="command translate" href="#" onclick="history.go()">refresh</a>
		<a id="logout" class="command translate" href="/?logout">logout</a>
		<!-- print, possibly plugin -->
		<!-- help, possibly plugin -->
		</div>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="contents">
		<xsl:param name="home">
			<xsl:call-template name="getReverseUrl">
				<xsl:with-param name="url" select="substring(/svn/index/@path, 2)"/>
			</xsl:call-template>
		</xsl:param>
		<div class="contents">
		<h2>
			<a href="{$home}">
				<span class="projectname">
					<xsl:call-template name="getProjectName"/>
				</span>
			</a>
			<xsl:value-of select="$spacer"/>
			<xsl:call-template name="getFolderPath"/>
			<xsl:value-of select="$spacer"/>
			<!-- not asked for by users <xsl:if test="@rev">
				<span class="revision">
					<xsl:value-of select="@rev"/>
				</span>
			</xsl:if> -->
		</h2>
		<xsl:apply-templates select="dir">
			<xsl:sort select="@name"/>
		</xsl:apply-templates>
		<xsl:apply-templates select="file">
			<xsl:sort select="@name"/>
		</xsl:apply-templates>
		</div>
	</xsl:template>
	<!-- generate directory -->
	<xsl:template match="dir">
		<xsl:param name="id">
			<xsl:call-template name="getFileID"/>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<div id="row:{$id}" class="row n{$n mod 4}">
			<div class="actions">
				<a id="open:{$id}" class="action" href="{@href}">open</a>
				<xsl:if test="$editUrl">
					<a id="rename:{$id}" class="action" href="{$editUrl}/rename/?path={../@path}/{@href}">rename</a>
					<span class="action">copy</span>
					<a id="delete:{$id}" class="action" href="{$editUrl}/delete/?path={../@path}/{@href}">delete</a>
				</xsl:if>
			</div>
			<a id="f:{$id}" class="folder" href="{@href}">
				<xsl:value-of select="@name"/>
			</a>
		</div>
	</xsl:template>
	<!-- generate file -->
	<xsl:template match="file">
		<xsl:param name="filetype">
			<xsl:call-template name="getFiletype"/>
		</xsl:param>
		<xsl:param name="id">
			<xsl:call-template name="getFileID"/>
		</xsl:param>
		<xsl:param name="n" select="count(/svn/index/dir) + position() - 1"/>
		<div id="row:{$id}" class="row n{$n mod 4}">
			<div class="actions">
				<a id="open:{$id}" class="action" title="this file can be opened in Repos" href="{$web}/open/?path={../@path}&amp;file={@href}">open</a>
				<xsl:if test="$editUrl">
					<a id="rename:{$id}" class="action" href="{$editUrl}/rename/?target={../@path}/{@href}">rename</a>
					<span class="action">copy</span>
					<a id="delete:{$id}" class="action" href="{$editUrl}/delete/?target={../@path}/{@href}">delete</a>
					<span class="action">lock</span>
					<a id="upload:{$id}" class="action" href="{$web}/upload/?target={../@path}/{@href}">upload changes</a>
				</xsl:if>
			</div>
			<a id="f:{$id}" class="file-{$filetype} file" href="{@href}">
				<xsl:value-of select="@name"/>
			</a>
		</div>
	</xsl:template>
	<!-- extra info and logos -->
	<xsl:template name="footer">
		<div class="footer">
		<span id="resourceversion" class="versiondisplay" style="display:none">repos.se stylesheet version $Id: repos.xsl 1601 2006-09-14 09:09:50Z solsson $</span>
		<span id="badges">
		</span>
		<span class="legal">
		<xsl:text>Powered by </xsl:text>
		<xsl:element name="a">
			<xsl:attribute name="href"><xsl:value-of select="../@href"/></xsl:attribute>
			<xsl:attribute name="target"><xsl:value-of select="'_blank'"/></xsl:attribute>
			<xsl:text>Subversion</xsl:text>
		</xsl:element>
		<xsl:text>&#160;</xsl:text>
		<xsl:value-of select="../@version"/>
		<xsl:text>&#160;</xsl:text>
		</span>
		<!-- quay button not used right now
		<span>
			<xsl:text>&#160;</xsl:text>
			<a id="quayButton"></a>
		</span> -->
		</div>
	</xsl:template>
	<!-- get the relative URL to the root of the project (the trunk folder) -->
	<xsl:template name="getTrunkUrl">
		<xsl:call-template name="getTrunkPath"/>
	</xsl:template>
	<!-- get the root folder name -->
	<xsl:template name="getProjectName">
		<xsl:param name="path" select="concat(/svn/index/@path,'/')"/>
		<xsl:value-of select="substring-before(substring($path,2),'/')"/>
	</xsl:template>
	<!-- get the folders as breadcrumbs (link each folder) -->
	<xsl:template name="getFolderPath">
		<xsl:param name="path" select="concat(/svn/index/@path,'/')"/>
		<xsl:param name="trunk">
			<xsl:call-template name="getTrunkPath">
				<xsl:with-param name="path" select="$path"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="p" select="substring($path, string-length($trunk)+2)"/>
		<xsl:call-template name="getFolderPathLinks">
			<xsl:with-param name="folders" select="$p"/>
		</xsl:call-template>
	</xsl:template>
	<!-- divide a path into its elements and make one link for each, expects folders to end with '/' -->
	<xsl:template name="getFolderPathLinks">
		<xsl:param name="folders"/>
		<xsl:param name="f" select="substring-before($folders, '/')"/>
		<xsl:param name="rest" select="substring-after($folders, concat($f,'/'))"/>
		<xsl:param name="return">
			<xsl:call-template name="getReverseUrl">
				<xsl:with-param name="url" select="$rest"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:if test="not(string-length($rest)>0)">
			<xsl:value-of select="$f"/>
			<xsl:value-of select="'/'"/>
		</xsl:if>
		<xsl:if test="string-length($rest)>0">
			<a href="{$return}">
				<xsl:value-of select="$f"/>
			</a>
			<xsl:value-of select="'/'"/>
			<xsl:call-template name="getFolderPathLinks">
				<xsl:with-param name="folders" select="$rest"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<!-- get the mandatory part of the repository, like /project/trunk. Dos not support branches yet. -->
	<!-- if 'trunk' is not a part of the path, return the first path element -->
	<xsl:template name="getTrunkPath">
		<xsl:param name="path" select="concat(/svn/index/@path,'/')"/>
		<xsl:param name="this" select="substring-before(substring($path, 2), '/')"/>
		<xsl:value-of select="$this"/>
		<xsl:value-of select="'/'"/>
		<xsl:choose>
			<xsl:when test="contains($this, 'trunk')">
			</xsl:when>
			<xsl:when test="not(contains($path, '/'))">
			</xsl:when>
			<xsl:when test="string-length($this)>0 and contains($path, '/trunk')">
				<xsl:call-template name="getTrunkPath">
					<xsl:with-param name="path" select="substring-after($path,$this)"/>
				</xsl:call-template>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
	<!-- get the path back, as multiple "../", to get to the path that uses 'url' to get to the current path -->
	<xsl:template name="getReverseUrl">
		<xsl:param name="url"/>
		<xsl:if test="contains($url,'/')">
			<xsl:value-of select="'../'"/>
			<xsl:call-template name="getReverseUrl">
				<xsl:with-param name="url" select="substring-after($url,'/')"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<!-- get file extension from attribute @href or param 'filename' -->
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
	<!-- make valid HTML id for file or folder, containing [A-Za-z0-9] and [-_.]  -->
	<!-- ids should always start with letters, so a prefix like 'f:' is needed -->
	<xsl:template name="getFileID">
		<xsl:param name="filename" select="@href"/>
		<xsl:value-of select="translate($filename,'%/','__')"/>
	</xsl:template>
	<!-- *** replace newline with <br> *** -->
	<xsl:template name="linebreak">
		<xsl:param name="text"/>
		<xsl:choose>
			<xsl:when test="contains($text, '&#10;')">
				<xsl:value-of select="substring-before($text, '&#10;')"/>
				<br/>
				<xsl:call-template name="linebreak">
					<xsl:with-param name="text" select="substring-after($text, '&#10;')"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$text"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
