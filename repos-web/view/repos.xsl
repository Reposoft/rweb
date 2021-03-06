<?xml version="1.0"?>
<!--
  ==== Repos Subversion directory listing layout ====
  Used as SVNIndexXSLT in repository configuration.
  (c) 2005-2011 Staffan Olsson www.repos.se
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<!-- start transform -->
	<xsl:output method="html" encoding="UTF-8" omit-xml-declaration="no" indent="no"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
	<xsl:param name="title">repos: <xsl:value-of select="/svn/index/@base"/></xsl:param>
	<!-- wrapping the config parameter with a different name, to be able to set it in a transformet -->
	<xsl:param name="web">/repos-web/</xsl:param>
	<xsl:param name="static"><xsl:value-of select="$web"/></xsl:param>
	<!-- static contents urls, set to {$web}style/ for default theme -->
	<xsl:param name="cssUrl"><xsl:value-of select="$static"/>style/</xsl:param>
	<!-- start url for simple WebDAV-like manipulation of repository, empty if not available -->
	<xsl:param name="editUrl">?rweb=e.</xsl:param>
	<!-- optional startpage to enable home button and special link for project, empty to disable -->
	<xsl:param name="startpage">/?rweb=start</xsl:param>
	<!-- link up, empty to hide up button -->
	<xsl:param name="parentlink">../</xsl:param>
	<!-- the recognized top level folders for project tools separated by slash -->
	<xsl:param name="tools">/trunk/branches/tags/tasks/templates/messages/calendar/administration/</xsl:param>
	<!-- special types of body contents, such as " static" (SVNParentPath) or " readonly" -->
	<xsl:param name="contentclass">
		<xsl:if test="/svn/index/@path = 'Collection of Repositories'"><xsl:value-of select="' static'"/></xsl:if>
	</xsl:param>
	<!-- document skeleton -->
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>
					<xsl:value-of select="$title"/>
					<xsl:value-of select="/svn/index/@path"/>
				</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<!-- if search crawlers has access, contents should not be cached -->
				<meta name="robots" content="noarchive"/>
				<link rel="shortcut icon" href="/favicon.ico"/>
				<!-- repos metadata -->
				<meta name="repos-service" content="index/" />
				<meta name="repos-target" content="{/svn/index/@path}/" />
				<meta name="repos-base" content="{/svn/index/@base}" />
				<!-- default stylesheets -->
				<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}global.css"/>
				<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}repository/repository.css"/>
				<!-- install the repos script bundle -->
				<script type="text/javascript" src="{$static}scripts/head.js"></script>
			</head>
			<body class="repository xml{$contentclass}">
				<xsl:apply-templates select="svn"/>
			</body>
		</html>
	</xsl:template>
	<xsl:template match="svn">
		<xsl:apply-templates select="index"/>
	</xsl:template>
	<!-- body contents -->
	<xsl:template match="index">
		<xsl:param name="folder">
			<xsl:call-template name="getHref">
				<xsl:with-param name="href" select="concat(/svn/index/@path,'/')"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="tool">
			<xsl:call-template name="getTool">
				<!-- todo limit depth in which to look for tools -->
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="pathlinks">
			<xsl:call-template name="getFolderPathLinks">
				<xsl:with-param name="base" select="/svn/index/@base"/>
				<xsl:with-param name="folders" select="$folder"/>
				<xsl:with-param name="toolcheck" select="string-length($tool)>1 or $tool='.'"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:call-template name="commandbar"/>
		<xsl:call-template name="contents">
			<xsl:with-param name="folder" select="$folder"/>
			<xsl:with-param name="pathlinks" select="$pathlinks"/>
			<xsl:with-param name="toolcheck" select="$tool='.'"/>
		</xsl:call-template>
		<xsl:call-template name="footer"/>
	</xsl:template>
	<!-- toolbar, directory actions -->
	<xsl:template name="commandbar">
		<div id="commandbar">
		<div class="right">
			<img id="logo" src="{$static}style/logo/R_Web1.png" border="0" alt="logo"/>
		</div>
		<xsl:if test="$startpage">
			<a id="start" class="command translate" href="{$startpage}">start</a>
		</xsl:if>
		<xsl:if test="boolean($parentlink)">
			<a id="parent" class="command translate" href="{$parentlink}">up</a>
		</xsl:if>
		<a id="view" class="command translate" href="?rweb=details">details</a>
		<a id="history" class="command translate" href="?rweb=history">history</a>
		<xsl:if test="$editUrl">
			<a id="createfolder" class="command translate" href="{$editUrl}mkdir">new&#xA0;folder</a>
			<a id="addfile" class="command translate" href="{$editUrl}upload">add&#xA0;file</a>
		</xsl:if>
		<a id="list" class="command translate" href="?rweb=list">change&#xA0;view</a>
		</div>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="contents">
		<xsl:param name="folder"/>
		<xsl:param name="pathlinks"/>
		<xsl:param name="toolcheck"/>
		<h2 id="path">
			<xsl:copy-of select="$pathlinks"/>
		</h2>
		<div class="readme readme-top reposmessage"></div>
		<!-- TODO add support or make this test more robust, for example work when there's no updir -->
		<xsl:if test="/svn/index/updir/@href = concat('../?p=', /svn/index/@rev)">
			<p class="warning">
				Browsing a specific revision. This is not fully supported so some links to Repos Web will fail.
				You may <a href="./">return to browsing latest</a> if this folder still exists.
			</p>
		</xsl:if>
		<div class="contentcommands"></div>
		<div class="contentdetails"></div>
		<ul class="index">
			<xsl:apply-templates select="dir">
				<xsl:sort select="@name"/>
				<xsl:with-param name="toolcheck" select="$toolcheck"/>
			</xsl:apply-templates>
			<xsl:apply-templates select="file">
				<xsl:sort select="@name"/>
			</xsl:apply-templates>
		</ul>
		<div class="readme readme-bottom"></div>
	</xsl:template>
	<!-- generate directory -->
	<xsl:template match="dir">
		<xsl:param name="id">
			<xsl:call-template name="getFileId"/>
		</xsl:param>
		<xsl:param name="href">
			<xsl:call-template name="getHref"/>
		</xsl:param>
		<xsl:param name="toolcheck"/>
		<xsl:param name="classadd">
			<xsl:if test="$toolcheck and contains($tools,@href)">
				<xsl:value-of select="concat(' tool tool-',@name)"/>
			</xsl:if>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<li id="row:{$id}" class="n{$n mod 4}{$classadd}">
			<a id="open:{$id}" class="folder{$classadd}" href="{$href}">
				<xsl:value-of select="@name"/>
			</a>
		</li>
	</xsl:template>
	<!-- generate file -->
	<xsl:template match="file">
		<xsl:param name="filetype">
			<xsl:call-template name="getFiletype"/>
		</xsl:param>
		<xsl:param name="id">
			<xsl:call-template name="getFileId"/>
		</xsl:param>
		<xsl:param name="href">
			<xsl:call-template name="getHref"/>
			<xsl:text>?rweb=details</xsl:text>
		</xsl:param>
		<xsl:param name="n" select="count(/svn/index/dir) + position() - 1"/>
		<li id="row:{$id}" class="n{$n mod 4}">
			<a id="open:{$id}" class="file-{$filetype} file" href="{$href}">
				<xsl:value-of select="@name"/>
			</a>
		</li>
	</xsl:template>
	<!-- extra info and logos -->
	<xsl:template name="footer">
		<div id="footer">
		<span>version <span class="revision"><xsl:value-of select="@rev"/></span></span>
		<span id="resourceversion" class="versiondisplay"> - repos.se stylesheet $Rev$</span>
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
	<!-- divide a path into its elements and make one link for each, expects folders to end with '/' -->
	<xsl:template name="getFolderPathLinks">
		<xsl:param name="base"/>
		<xsl:param name="folders"/>
		<xsl:param name="toolcheck"/>
		<xsl:param name="f" select="substring-before($folders, '/')"/>
		<xsl:param name="rest" select="substring-after($folders, concat($f,'/'))"/>
		<xsl:param name="return">
			<xsl:call-template name="getReverseUrl">
				<xsl:with-param name="url" select="$rest"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="classadd">
			<xsl:choose>
				<xsl:when test="$toolcheck and contains($tools,concat('/',$f,'/'))">
					<xsl:value-of select="concat(' tool tool-',$f)"/>
				</xsl:when>
				<xsl:when test="$toolcheck">
					<xsl:value-of select="' projectname'"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="''"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:param>
		<xsl:param name="id">
			<xsl:call-template name="getFileId">
				<xsl:with-param name="filename" select="$return"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:if test="$base">
			<a id="base" class="repo" href="{$return}"><xsl:value-of select="$base"/></a>
		</xsl:if>
		<xsl:if test="not($rest)">
			<a id="realurl" href="./" class="path{$classadd}">
				<xsl:value-of select="$f"/>
				<xsl:if test="not($f)">
					<xsl:text>/</xsl:text>
				</xsl:if>
			</a>
		</xsl:if>
		<xsl:if test="boolean($rest)">
			<xsl:if test="boolean($f)">
				<a id="{$id}" href="{$return}" class="path{$classadd}">
					<xsl:value-of select="$f"/>
				</a>
			</xsl:if>
			<span class="separator"><xsl:value-of select="'/'"/></span>
			<xsl:call-template name="getFolderPathLinks">
				<xsl:with-param name="folders" select="$rest"/>
				<xsl:with-param name="return" select="substring-after($return,'/')"/>
				<xsl:with-param name="toolcheck" select="$toolcheck and not(contains($classadd,' tool '))"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<!-- check if path contains an element that matches a tool name -->
	<xsl:template name="getTool">
		<xsl:param name="list" select="$tools"/>
		<xsl:param name="folder" select="concat(/svn/index/@path,'/')"/>
		<xsl:param name="check" select="concat(substring-before($list,'/'),'/')"/>
		<xsl:choose>
			<xsl:when test="string-length($check)>1 and contains($folder,concat('/',$check))">
 				<xsl:value-of select="substring-before($check,'/')"/>
			</xsl:when>
			<xsl:when test="/svn/index/dir[@href=$check]">
				<xsl:value-of select="'.'"/>
			</xsl:when>
			<xsl:when test="boolean($list)">
				<xsl:call-template name="getTool">
					<xsl:with-param name="list" select="substring-after($list,'/')"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
			</xsl:otherwise>
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
	<!-- make valid HTML id for file or folder, matching [A-Za-z][A-Za-z0-9:_.-]*  -->
	<!-- ids should always start with letters, so a prefix like 'file:' must be prepended -->
	<xsl:template name="getFileId">
		<xsl:param name="filename" select="@href"/>
		<xsl:value-of select="translate($filename,'%/()@&amp;+=,~$!','____________')"/>
	</xsl:template>
	<!-- subversion has some shortcomings in urlescaping, which we compensate for here -->
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
			<xsl:otherwise>
				<xsl:value-of select="$href"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
