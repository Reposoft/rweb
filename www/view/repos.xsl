<?xml version="1.0"?>
<!--
  ==== Repos Subversion directory listing layout ====
  Used as SVNIndexXSLT in repository configuration.
  (c) 2005-2007 Staffan Olsson www.repos.se

  Note that browser transformations only work if the
  stylesheet is read from the same domain as the XML
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<!-- start transform -->
	<xsl:output method="html" encoding="UTF-8" omit-xml-declaration="no" indent="no"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
	<!-- wrapping the config parameter with a different name, to be able to set it in a transformet -->
	<xsl:param name="web">/repos/</xsl:param>
	<xsl:param name="static">/repos/</xsl:param>
	<!-- static contents urls, set to {$web}style/ for default theme -->
	<xsl:param name="cssUrl"><xsl:value-of select="$static"/>style/</xsl:param>
	<xsl:param name="cssUrl-pe"><xsl:value-of select="$static"/>themes/pe/style/</xsl:param>
	<!-- start url for simple WebDAV-like manipulation of repository, empty if not available -->
	<xsl:param name="editUrl"><xsl:value-of select="$web"/>edit/</xsl:param>
	<!-- we don't want to force the CSS to set margins everywhere -->
	<xsl:param name="spacer" select="' &#160; '"/>
	<!-- starpage to use as parent directory of 'trunk' -->
	<xsl:param name="startpage"><xsl:value-of select="$web"/>open/start/</xsl:param>
	<!-- TODO followConversions: maintain repository conversions, meaning that:
	- 'trunk',' branches', 'tags' can not be renamed or removed
	-  (actually nothing in the same dir as 'trunk' can be renamed or removed).
	- There is no button to go to parent folder in trunk.
	- If there is such a button it leads to a start page where the folders that the user has access to are listed.
	- The subdirectories of 'branches' and 'tags' are treated as 'trunk' (no remove, no go up).
	- These rules are void if there is a 'trunk', 'branches' or 'tags' in the parent path of the folder
	-  (so that it is legal to make a folder named 'tags' inside a project).
	- Of course these rules only apply to this web client, they are not enforced in the repository.
	- Implementing this affects the choice of $parentpath and the places where we check for $editUrl.
	-->
	<!-- document skeleton -->
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>
					<xsl:text>repos: </xsl:text>
					<xsl:value-of select="/svn/index/@path"/>
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
				<script type="text/javascript" src="{$static}scripts/head.js"></script>
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
		<xsl:param name="fullpath" select="concat(/svn/index/@path,'/')"/>
		<xsl:param name="tool">
			<xsl:call-template name="getToolPath">
				<xsl:with-param name="path" select="$fullpath"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="folders" select="substring($fullpath, string-length($tool)+2)"/>
		<xsl:param name="homelink">
			<xsl:call-template name="getReverseUrl">
				<xsl:with-param name="url" select="$folders"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="projectname">
			<xsl:call-template name="getProjectName"/>
		</xsl:param>
		<xsl:param name="pathlinks">
			<xsl:call-template name="getFolderPathLinks">
				<xsl:with-param name="folders" select="$folders"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:call-template name="commandbar">
			<xsl:with-param name="parentlink">
				<xsl:choose>
					<xsl:when test="string-length($startpage)>0 and $pathlinks='/'">
						<xsl:value-of select="$startpage"/>
					</xsl:when>
					<xsl:when test="string-length($startpage)>0 and not($tool)">
						<xsl:value-of select="$startpage"/>
					</xsl:when>
					<xsl:when test="/svn/index/updir">../</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:with-param>
		</xsl:call-template>
		<xsl:call-template name="contents">
			<xsl:with-param name="fullpath" select="$fullpath"/>
			<xsl:with-param name="tool" select="$tool"/>
			<xsl:with-param name="homelink" select="$homelink"/>
			<xsl:with-param name="projectname" select="$projectname"/>
			<xsl:with-param name="pathlinks" select="$pathlinks"/>
		</xsl:call-template>
		<xsl:call-template name="footer"/>
	</xsl:template>
	<!-- toolbar, directory actions -->
	<xsl:template name="commandbar">
		<xsl:param name="parentlink"/>
		<div id="commandbar">
			<img src="{$static}style/logo/repos1.png" border="0" align="right" width="72" height="18" alt="repos.se" title="Using repos.se stylesheet $Rev$"/>
		<xsl:if test="string-length($parentlink)>0">
			<a id="parent" class="command translate" href="{$parentlink}">up</a>
		</xsl:if>
		<xsl:if test="$editUrl">
			<a id="createfolder" class="command translate" href="{$editUrl}mkdir/?target={@path}/">new folder</a>
			<a id="createfile" class="command translate" href="{$editUrl}file/?target={@path}/">new document</a>
			<a id="addfile" class="command translate" href="{$editUrl}upload/?target={@path}/">add file</a>
		</xsl:if>
		<a id="history" class="command translate" href="{$web}open/log/?target={@path}/">folder history</a>
		<a id="refresh" class="command translate" href="#" onclick="window.location.reload( true )">refresh</a>
		<a id="logout" class="command translate" href="/?logout">logout</a>
		<!-- print, possibly plugin -->
		<!-- help, possibly plugin -->
		</div>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="contents">
		<xsl:param name="fullpath"/>
		<xsl:param name="tool"/>
		<xsl:param name="projectname"/>
		<xsl:param name="homelink"/>
		<xsl:param name="pathlinks"/>
		<span id="fullpath" style="display:none"><xsl:value-of select="$fullpath"/></span>
		<h2>
			<a id="home" href="{$homelink}">
				<span class="projectname">
					<xsl:value-of select="$projectname"/>
				</span>
			</a>
			<xsl:value-of select="$spacer"/>
			<xsl:copy-of select="$pathlinks"/>
		</h2>
		<xsl:apply-templates select="dir">
			<xsl:sort select="@name"/>
		</xsl:apply-templates>
		<xsl:apply-templates select="file">
			<xsl:sort select="@name"/>
		</xsl:apply-templates>
	</xsl:template>
	<!-- generate directory -->
	<xsl:template match="dir">
		<xsl:param name="id">
			<xsl:call-template name="getFileID"/>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<div id="row:{$id}" class="row n{$n mod 4}">
			<div class="actions">
				<a id="view:{$id}" class="action" href="{@href}">view</a>
				<xsl:if test="$editUrl">
					<a id="rename:{$id}" class="action" href="{$editUrl}rename/?target={../@path}/{@href}">rename</a>
					<a id="copy:{$id}" class="action"  href="{$editUrl}copy/?target={../@path}/{@href}">copy</a>
					<a id="delete:{$id}" class="action" href="{$editUrl}delete/?target={../@path}/{@href}">delete</a>
				</xsl:if>
			</div>
			<a id="open:{$id}" class="folder" href="{@href}">
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
				<a id="view:{$id}" class="action" href="{$web}open/?target={../@path}/{@href}">view</a>
				<xsl:if test="$editUrl">
					<a id="edit:{$id}" class="action" href="{$editUrl}?target={../@path}/{@href}">edit</a>
					<a id="rename:{$id}" class="action" href="{$editUrl}rename/?target={../@path}/{@href}">rename</a>
					<a id="copy:{$id}" class="action" href="{$editUrl}copy/?target={../@path}/{@href}">copy</a>
					<a id="delete:{$id}" class="action" href="{$editUrl}delete/?target={../@path}/{@href}">delete</a>
					<a id="upload:{$id}" class="action" href="{$editUrl}upload/?target={../@path}/{@href}">upload changes</a>
				</xsl:if>
				<a id="history:{$id}" class="action" href="{$web}open/log/?target={../@path}/{@href}">view history</a>
			</div>
			<a id="open:{$id}" class="file-{$filetype} file" href="{@href}">
				<xsl:value-of select="@name"/>
			</a>
		</div>
	</xsl:template>
	<!-- extra info and logos -->
	<xsl:template name="footer">
		<div id="footer">
		<span>version <span class="revision"><xsl:value-of select="@rev"/></span> - </span>
		<span id="resourceversion" class="versiondisplay" style="display:none">repos.se stylesheet $URL$ $Rev$</span>
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
		</div>
	</xsl:template>
	<!-- get the root folder name -->
	<xsl:template name="getProjectName">
		<xsl:param name="path" select="concat(/svn/index/@path,'/')"/>
		<xsl:value-of select="substring-before(substring($path,2),'/')"/>
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
		<xsl:param name="id">
			<xsl:call-template name="getFileID">
				<xsl:with-param name="filename" select="$return"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:if test="not(string-length($rest)>0)">
			<span id="folder" class="path">
				<xsl:value-of select="$f"/>
				<xsl:value-of select="'/'"/>
			</span>
		</xsl:if>
		<xsl:if test="string-length($rest)>0">
			<a id="{$id}" href="{$return}">
				<xsl:value-of select="$f"/>
			</a>
			<xsl:value-of select="'/'"/>
			<xsl:call-template name="getFolderPathLinks">
				<xsl:with-param name="folders" select="$rest"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<!-- get the mandatory part of the repository, like /project/trunk. Dos not support branches yet. -->
	<!-- if 'trunk' is not a part of the path, or is the first part, return the first path element -->
	<xsl:template name="getToolPath">
		<xsl:param name="path" select="concat(/svn/index/@path,'/')"/>
		<xsl:param name="this" select="substring-before(substring($path, 2), '/')"/>
		<xsl:value-of select="$this"/>
		<xsl:value-of select="'/'"/>
		<xsl:choose>
			<xsl:when test="contains($this, 'trunk') or contains($this, 'administration')">
			</xsl:when>
			<xsl:when test="not(contains($path, '/'))">
			</xsl:when>
			<xsl:when test="string-length($this)>0 and (contains($path, '/trunk') or contains($path, '/administration'))">
				<xsl:call-template name="getToolPath">
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
	<!-- ids should always start with letters, so a prefix like 'file:' must be prepended -->
	<xsl:template name="getFileID">
		<xsl:param name="filename" select="@href"/>
		<xsl:value-of select="translate($filename,'%/()@&amp;','______')"/>
	</xsl:template>
</xsl:stylesheet>
