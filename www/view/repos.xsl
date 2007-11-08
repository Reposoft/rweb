<?xml version="1.0"?>
<!--
  ==== Repos Subversion directory listing layout ====
  Used as SVNIndexXSLT in repository configuration.
  (c) 2005-2007 Staffan Olsson www.repos.se
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<!-- start transform -->
	<xsl:output method="html" encoding="UTF-8" omit-xml-declaration="no" indent="no"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
	<xsl:param name="title">repos: </xsl:param>
	<!-- wrapping the config parameter with a different name, to be able to set it in a transformet -->
	<xsl:param name="web">/repos-web/</xsl:param>
	<xsl:param name="static">/repos-web/</xsl:param>
	<!-- static contents urls, set to {$web}style/ for default theme -->
	<xsl:param name="cssUrl"><xsl:value-of select="$static"/>style/</xsl:param>
	<!-- start url for simple WebDAV-like manipulation of repository, empty if not available -->
	<xsl:param name="editUrl"><xsl:value-of select="$web"/>edit/</xsl:param>
	<!-- we don't want to force the CSS to set margins everywhere -->
	<xsl:param name="spacer" select="' &#160; '"/>
	<!-- optional startpage to enable home button and special link for project, empty to disable -->
	<xsl:param name="startpage"><xsl:value-of select="$web"/>open/start/</xsl:param>
	<!-- the recognized top level folders for project tools separated by slash -->
	<xsl:param name="tools">/trunk/branches/tags/tasks/templates/messages/calendar/administration/</xsl:param>
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
				<!-- default stylesheets -->
				<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}global.css"/>
				<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}repository/repository.css"/>
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
		<xsl:param name="folder">
			<xsl:call-template name="getHref">
				<xsl:with-param name="href" select="concat(/svn/index/@path,'/')"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="pathlinks">
			<xsl:call-template name="getFolderPathLinks">
				<xsl:with-param name="folders" select="substring($folder,2)"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:call-template name="commandbar">
			<xsl:with-param name="target" select="$folder"/>
			<xsl:with-param name="parentlink">
				<xsl:choose>
					<xsl:when test="/svn/index/updir">../</xsl:when>
					<xsl:when test="string-length($startpage)>0">
						<xsl:value-of select="$startpage"/>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:with-param>
		</xsl:call-template>
		<xsl:call-template name="contents">
			<xsl:with-param name="folder" select="$folder"/>
			<xsl:with-param name="pathlinks" select="$pathlinks"/>
		</xsl:call-template>
		<xsl:call-template name="footer"/>
	</xsl:template>
	<!-- toolbar, directory actions -->
	<xsl:template name="commandbar">
		<xsl:param name="parentlink"/>
		<xsl:param name="target"/>
		<div id="commandbar">
		<div class="right">
		<a id="logout" class="command translate" href="/?logout">logout</a>
		<img src="{$static}style/logo/repos1.png" border="0" width="72" height="18" alt="repos.se" title="Using repos.se stylesheet $Rev$"/>
		</div>
		<xsl:if test="$startpage">
			<a id="start" class="command translate" href="{$startpage}">start</a>
		</xsl:if>
		<xsl:if test="string-length($parentlink)>0">
			<a id="parent" class="command translate" href="{$parentlink}">up</a>
		</xsl:if>
		<xsl:if test="$editUrl">
			<a id="createfolder" class="command translate" href="{$editUrl}mkdir/?target={$target}">new&#xA0;folder</a>
			<a id="addfile" class="command translate" href="{$editUrl}upload/?target={$target}">add&#xA0;file</a>
		</xsl:if>
		<a id="history" class="command translate" href="{$web}open/log/?target={$target}">folder&#xA0;history</a>
		<a id="refresh" class="command translate" href="#" onclick="window.location.reload( true )">refresh</a>
		<!-- print, possibly plugin -->
		<!-- help, possibly plugin -->
		</div>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="contents">
		<xsl:param name="folder"/>
		<xsl:param name="pathlinks"/>
		<!-- it is not trivial to check if a tool has already been found in path, so instead we assume that tools are only in project root -->
		<xsl:param name="trytools" select="not(substring-after(substring(@path,2),'/'))"/>
		<h2 id="path">
			<xsl:copy-of select="$pathlinks"/>
		</h2>
		<xsl:apply-templates select="dir">
			<xsl:sort select="@name"/>
			<xsl:with-param name="trytools" select="$trytools"/>
		</xsl:apply-templates>
		<xsl:apply-templates select="file">
			<xsl:sort select="@name"/>
		</xsl:apply-templates>
		<div class="row contentcommands">
			<div class="actions">
				<xsl:if test="$editUrl">
					<a id="repos-edit" class="translate" href="{$editUrl}file/?target={$folder}">Create new text</a>
				</xsl:if>
			</div>
		</div>
	</xsl:template>
	<!-- generate directory -->
	<xsl:template match="dir">
		<xsl:param name="id">
			<xsl:call-template name="getFileId"/>
		</xsl:param>
		<xsl:param name="href">
			<xsl:call-template name="getHref"/>
		</xsl:param>
		<xsl:param name="target">
			<xsl:call-template name="getHref">
				<xsl:with-param name="href">
					<xsl:value-of select="../@path"/>
					<xsl:value-of select="'/'"/>
					<xsl:value-of select="@href"/>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="trytools"/>
		<xsl:param name="classadd">
			<xsl:if test="$trytools and contains($tools,@href)">
				<xsl:value-of select="concat(' tool tool-',@name)"/>
			</xsl:if>
		</xsl:param>
		<xsl:param name="n" select="position() - 1"/>
		<div id="row:{$id}" class="row n{$n mod 4}{$classadd}">
			<div class="actions">
				<a id="view:{$id}" class="action action-view" href="{$href}">view</a>
				<xsl:if test="$editUrl">
					<a id="copy:{$id}" class="action action-copy"  href="{$editUrl}copy/?target={$target}">copy</a>
					<a id="rename:{$id}" class="action action-rename" href="{$editUrl}rename/?target={$target}">rename</a>
					<a id="delete:{$id}" class="action action-delete" href="{$editUrl}delete/?target={$target}">delete</a>
				</xsl:if>
			</div>
			<a id="open:{$id}" class="folder{$classadd}" href="{$href}">
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
			<xsl:call-template name="getFileId"/>
		</xsl:param>
		<xsl:param name="href">
			<xsl:call-template name="getHref"/>
		</xsl:param>
		<xsl:param name="target">
			<xsl:call-template name="getHref">
				<xsl:with-param name="href">
					<xsl:value-of select="../@path"/>
					<xsl:value-of select="'/'"/>
					<xsl:value-of select="@href"/>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="n" select="count(/svn/index/dir) + position() - 1"/>
		<div id="row:{$id}" class="row n{$n mod 4}">
			<div class="actions">
				<a id="view:{$id}" class="action" href="{$web}open/?target={$target}">view</a>
				<xsl:if test="$editUrl">
					<a id="edit:{$id}" class="action" href="{$editUrl}?target={$target}">edit</a>
					<a id="copy:{$id}" class="action" href="{$editUrl}copy/?target={$target}">copy</a>
					<a id="rename:{$id}" class="action" href="{$editUrl}rename/?target={$target}">rename</a>
					<a id="delete:{$id}" class="action" href="{$editUrl}delete/?target={$target}">delete</a>
					<a id="upload:{$id}" class="action" href="{$editUrl}upload/?target={$target}">upload&#xA0;changes</a>
				</xsl:if>
				<a id="history:{$id}" class="action" href="{$web}open/log/?target={$target}">view&#xA0;history</a>
			</div>
			<a id="open:{$id}" class="file-{$filetype} file" href="{$href}">
				<xsl:value-of select="@name"/>
			</a>
		</div>
	</xsl:template>
	<!-- extra info and logos -->
	<xsl:template name="footer">
		<div id="footer">
		<span>version <span class="revision"><xsl:value-of select="@rev"/></span></span>
		<span id="resourceversion" class="versiondisplay" style="display:none"> - repos.se stylesheet @Dev@ $Rev$</span>
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
		<xsl:param name="classadd">
			<xsl:if test="contains($tools,concat('/',$f,'/'))">
				<xsl:value-of select="concat(' tool tool-',$f)"/>
			</xsl:if>
		</xsl:param>
		<xsl:param name="id">
			<xsl:call-template name="getFileId">
				<xsl:with-param name="filename" select="$return"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:if test="not(string-length($rest)>0)">
			<span id="folder" class="path{$classadd}">
				<xsl:value-of select="$f"/>
				<xsl:value-of select="'/'"/>
			</span>
		</xsl:if>
		<xsl:if test="string-length($rest)>0">
			<a id="{$id}" href="{$return}" class="path{$classadd}">
				<xsl:value-of select="$f"/>
			</a>
			<xsl:value-of select="'/'"/>
			<xsl:if test="string-length($classadd)=0">
				<xsl:call-template name="getFolderPathLinks">
					<xsl:with-param name="folders" select="$rest"/>
					<xsl:with-param name="return" select="substring-after($return,'/')"/>
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="string-length($classadd)>0">
				<xsl:call-template name="getFolderPathLinks">
					<xsl:with-param name="folders" select="$rest"/>
					<xsl:with-param name="return" select="substring-after($return,'/')"/>
					<xsl:with-param name="classadd" select="' toolchild'"/>
				</xsl:call-template>
			</xsl:if>
		</xsl:if>
	</xsl:template>
	<!-- get the home folder, like /project/trunk/ or /branches/, see $tools --> 
	<!-- <xsl:template name="getToolPath">
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
	</xsl:template> -->
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
