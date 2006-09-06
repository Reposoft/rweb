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
	<!-- import configuration, since we can't set parameters in the browsers -->
	<xsl:import href="/repos/view/conf.xsl"/>
	<!-- start transform -->
	<xsl:output method="xml" indent="no"/>
	<!-- wrapping the config parameter with a different name, to be able to set it in a transformet -->
	<xsl:param name="web"><xsl:value-of select="$repos_web"/></xsl:param>
	<xsl:param name="repo"><xsl:value-of select="$repo_url"/></xsl:param>
	<!-- static contents urls -->
	<xsl:param name="cssUrl"><xsl:value-of select="$web"/><xsl:value-of select="$theme"/>/style</xsl:param>
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
				<!-- if google may index the repository (googlebot.com has access), contents should not be cached -->
				<meta name="robots" content="noarchive"/>
				<!-- default stylesheet -->
				<link rel="stylesheet" type="text/css" href="{$cssUrl}/global.css"/>
				<link rel="stylesheet" type="text/css" href="{$cssUrl}/index/index.css"/>
				<link rel="shortcut icon" href="http://www.repos.se/favicon.ico"/>
				<!-- install repos-quay, this row and an icon in the footer -->
				<script type="text/javascript" src="/quay/repos-quay.js"></script>
				<!-- install the repos script bundle -->
				<script type="text/javascript" src="{$web}/scripts/head.js"></script>
			</head>
			<body class="index">
				<!-- supported contents -->
				<xsl:apply-templates select="svn"/>
				<xsl:apply-templates select="log"/>
				<xsl:apply-templates select="diff"/>
				<xsl:apply-templates select="cat"/>
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
		<div class="commandbar">
		<a id="reposbutton" href="http://www.repos.se/" target="_blank">
			<img src="{$web}/style/logo/repos1.png" border="0" align="right" width="72" height="18"/>
		</a>
		<a id="refresh" class="command translage" href="#" onclick="history.go()">refresh</a>
		<xsl:if test="$editUrl">
			<a id="newfolder" class="command translate" href="{$editUrl}/?action=mkdir&amp;path={@path}">new folder</a>
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
		<a id="showlog" class="command translate" href="{$web}/open/log/?path={@path}">show log</a>
		<a id="logout" class="command translate" href="/?logout">logout</a>
		<!-- print, possibly plugin -->
		<!-- help, possibly plugin -->
		</div>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="contents">
		<xsl:param name="home">
			<xsl:call-template name="getTrunkUrl"/>
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
			<xsl:if test="@rev">
				<span class="revision">
					<xsl:value-of select="@rev"/>
				</span>
			</xsl:if>
		</h2>
		<xsl:if test="/svn/index/updir">
			<xsl:if test="not($disable-up-at-trunk='yes' and substring-after(/svn/index/@path, '/trunk')='')">
				<div class="row">
					<a id="up" class="translate" href="../">up</a>
				</div>
			</xsl:if>
		</xsl:if>
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
		<div class="row">
			<a id="{$id}" class="folder" href="{@href}">
				<xsl:value-of select="@name"/>
			</a>
			<xsl:value-of select="$spacer"/>
			<div class="actions">
				<a class="action" href="{@href}">open</a>
				<xsl:if test="$editUrl">
					<a class="action" href="{$editUrl}/?action=rename&amp;path={../@path}/{@href}">rename</a>
					<span class="action">copy</span>
					<a class="action" href="{$editUrl}/?action=delete&amp;path={../@path}/{@href}">delete</a>
				</xsl:if>
			</div>
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
		<div class="row">
			<a id="{$id}" class="file file-{$filetype}" href="{@href}">
				<xsl:value-of select="@name"/>
			</a>
			<xsl:value-of select="$spacer"/>
			<div class="actions">
				<a class="action" title="this file can be opened in Repos" href="{$web}/open/?path={../@path}&amp;file={@href}">open</a>
				<xsl:if test="$editUrl">
					<a class="action" href="{$editUrl}/?action=rename&amp;path={../@path}&amp;file={@href}">rename</a>
					<span class="action">copy</span>
					<a class="action" href="{$editUrl}/?action=delete&amp;path={../@path}&amp;file={@href}">delete</a>
					<span class="action">lock</span>
					<a class="action" href="{$web}/upload/?path={../@path}&amp;file={@href}">upload changes</a>
				</xsl:if>
			</div>
		</div>
	</xsl:template>
	<!-- extra info and logos -->
	<xsl:template name="footer">
		<div class="footer">
		<span class="path">
			<xsl:call-template name="getTrunkUrl"/>
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
	<!-- get the absolute URL for the project's file archove, using configuration -->
	<xsl:template name="getTrunkUrl">
		<xsl:value-of select="$repo"/>
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
		<xsl:param name="basepath">
			<xsl:call-template name="getTrunkPath">
				<xsl:with-param name="path" select="$path"/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="p" select="substring-after($path, $basepath)"/>
		<xsl:call-template name="getFolderPathLinks">
			<xsl:with-param name="folders" select="$p"/>
			<xsl:with-param name="url">
				<xsl:call-template name="getTrunkUrl"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<!-- divide a path into its elements and make one link for each, expects folders to end with '/' -->
	<xsl:template name="getFolderPathLinks">
		<xsl:param name="folders"/>
		<xsl:param name="url"/>
		<xsl:param name="f" select="substring-before($folders, '/')"/>
		<xsl:param name="rest" select="substring-after($folders, concat($f,'/'))"/>
		<xsl:if test="not(string-length($rest)>0)">
			<xsl:value-of select="$f"/>
			<xsl:value-of select="'/'"/>
		</xsl:if>
		<xsl:if test="string-length($rest)>0">
			<a href="{$url}{$f}/">
				<xsl:value-of select="$f"/>
			</a>
			<xsl:value-of select="'/'"/>
			<xsl:call-template name="getFolderPathLinks">
				<xsl:with-param name="folders" select="$rest"/>
				<xsl:with-param name="url">
					<xsl:value-of select="$url"/>
					<xsl:value-of select="$f"/>
					<xsl:value-of select="'/'"/>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<!-- get the mandatory part of the repository, like /project/trunk. Dos not support branches yet. -->
	<xsl:template name="getTrunkPath">
		<xsl:param name="path" select="concat(/svn/index/@path,'/')"/>
		<xsl:param name="this" select="substring-before($path, '/')"/>
		<xsl:value-of select="$this"/>
		<xsl:value-of select="'/'"/>
		<xsl:choose>
			<xsl:when test="contains($this, 'trunk')">
			</xsl:when>
			<xsl:when test="not(contains($path, '/'))">
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="getTrunkPath">
					<xsl:with-param name="path" select="substring-after($path,'/')"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
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
	<!-- make a valid HTML id (starting with character, then characters, digits or -_:. -->
	<xsl:template name="getFileID">
		<xsl:param name="filename" select="@href"/>
		<xsl:value-of select="'f:'"/>
		<xsl:value-of select="translate($filename,'%','_')"/>
	</xsl:template>
	<!--
	========= svn log xml formatting ==========
	-->
	<!-- text strings -->
	<xsl:param name="show-diff">Show changes (differeces from previous revision)</xsl:param>
	<xsl:param name="undo">Reverse the changes made from previous revision to this one</xsl:param>
	<!-- layout -->
	<xsl:template match="log">
		<table class="info" width="98%" align="center">
			<tr>
				<td id="titlebar" class="titlebar">
					<xsl:call-template name="titlebar"/>
				</td>
			</tr>
			<tr>
				<td id="commandbar" class="commandbar">
					<a id="up" class="command" href="{@repo}{@path}">up</a>
				</td>
			</tr>
			<tr>
				<td id="workarea" class="workarea">
					<h1>
						<span>Log</span>
					</h1>
					<xsl:apply-templates select="error"/>
					<xsl:apply-templates select="logentry"/>
				</td>
			</tr>
			<tr>
				<td id="footer" class="footer">
					<xsl:value-of select="$spacer"/>
				</td>
			</tr>
		</table>
	</xsl:template>
	<xsl:template match="logentry">
		<div id="rev{@revision}">
			<h3>
				<span class="revision">
					<xsl:value-of select="@revision"/>
				</span>
				<xsl:value-of select="$spacer"/>
				<span class="username">
					<xsl:value-of select="author"/>
				</span>
				<xsl:value-of select="$spacer"/>
				<span class="datetime">
					<xsl:value-of select="date"/>
				</span>
				<xsl:value-of select="$spacer"/>
				<a title="{$undo}" class="action" href="{$web}/edit/undo/?repo={../@repo}&amp;rev={@revision}">undo</a>
			</h3>
			<xsl:if test="string-length(msg) > 0">
				<p>
					<span title="log message">
						<xsl:call-template name="logicon">
							<xsl:with-param name="name" select="'_message'"/>
						</xsl:call-template>
					</span>
					<span class="message">
						<xsl:call-template name="linebreak">
							<xsl:with-param name="text" select="msg"/>
						</xsl:call-template>
					</span>
				</p>
			</xsl:if>
			<xsl:apply-templates select="paths">
				<xsl:with-param name="revfrom" select="following-sibling::*[1]/@revision"/>
			</xsl:apply-templates>
		</div>
	</xsl:template>
	<xsl:template match="paths">
		<xsl:param name="revfrom"/>
		<xsl:apply-templates select="path">
			<xsl:with-param name="revfrom" select="$revfrom"/>
		</xsl:apply-templates>
	</xsl:template>
	<xsl:template match="paths/path">
		<xsl:param name="revfrom"/>
		<p>
			<xsl:if test="@action='A'">
				<span title="{@action} - added">
					<xsl:call-template name="logicon">
						<xsl:with-param name="name" select="'_a'"/>
					</xsl:call-template>
				</span>
				<span class="path">
					<xsl:value-of select="."/>
				</span>
				<xsl:value-of select="$spacer"/>
				<xsl:if test="@copyfrom-path">
					<span title="copied from">
						<xsl:call-template name="logicon">
							<xsl:with-param name="name" select="'_copiedfrom'"/>
						</xsl:call-template>
					</span>
					<span class="path">
						<xsl:value-of select="@copyfrom-path"/>&#160;</span>
					<span class="revision">
						<xsl:value-of select="@copyfrom-rev"/>
					</span>
				</xsl:if>
			</xsl:if>
			<xsl:if test="@action='D'">
				<span title="{@action} - deleted">
					<xsl:call-template name="logicon">
						<xsl:with-param name="name" select="'_d'"/>
					</xsl:call-template>
				</span>
				<span class="path">
					<xsl:value-of select="."/>
				</span>
			</xsl:if>
			<xsl:if test="@action='M'">
				<a title="{@action} - {$show-diff}" href="{$web}/open/diff/?repo={../../../@repo}&amp;target={.}&amp;revto={../../@revision}&amp;revfrom={$revfrom}">
					<xsl:call-template name="logicon">
						<xsl:with-param name="name" select="'_m'"/>
					</xsl:call-template>
				</a>
				<span class="path">
					<xsl:value-of select="."/>
				</span>
				<xsl:value-of select="$spacer"/>
				<a class="action" href="{$web}/open/cat/?repo={../../../@repo}&amp;target={.}&amp;rev={$revfrom}">before</a>
				<a class="action" href="{$web}/open/cat/?repo={../../../@repo}&amp;target={.}&amp;rev={../../@revision}">after</a>
			</xsl:if>
			<xsl:if test="@action='A'">
				
			</xsl:if>
		</p>
	</xsl:template>
	<xsl:template name="logicon">
		<xsl:param name="name"/>
		<img src="{$iconsUrl}/{$name}.{$iconType}" border="0" align="absmiddle" width="{$miniIconSize}" height="{$miniIconSize}" hspace="{$iconHspace}" vspace="{$iconVspace}"/>
	</xsl:template>
	<!--
	========= svn diff formatting ==========
	-->
	<xsl:template match="diff">
		<table class="info" width="98%" align="center">
			<tr>
				<td id="titlebar" class="titlebar">
					<xsl:call-template name="titlebar"/>
				</td>
			</tr>
			<tr>
				<td id="commandbar" class="commandbar">
					<a id="up" class="command" href="#" onclick="history.back()">back</a>
				</td>
			</tr>
			<tr>
				<td id="workarea" class="workarea">
					<h2>
						<span class="filename">
							<xsl:value-of select="@target"/>
						</span>
						<xsl:value-of select="$spacer"/>
						<span class="revision">
							<xsl:value-of select="@revfrom"/>
						</span>
						<xsl:value-of select="':'"/>
						<span class="revision">
							<xsl:value-of select="@revto"/>
						</span>
					</h2>
					<xsl:apply-templates select="error">
						<xsl:with-param name="possible-cause">The file might have been moved from this location</xsl:with-param>
					</xsl:apply-templates>
					<xsl:apply-templates select="plaintext"/>
				</td>
			</tr>
			<tr>
				<td id="footer" class="footer">
					<xsl:value-of select="$spacer"/>
				</td>
			</tr>
		</table>
	</xsl:template>
	<!--
	========= special contents ==========
	-->
	<xsl:template match="plaintext">
		<pre>
			<xsl:value-of select="."/>
		</pre>
	</xsl:template>
	<xsl:template match="display">
		<iframe src="{@src}" name="displayFrame" width="100%" height="100%" scrolling="auto">
		</iframe>
	</xsl:template>
	<!--
	========= svn cat formatting ==========
	-->
	<xsl:template match="cat">
		<table class="info" width="98%" align="center">
			<tr>
				<td id="titlebar" class="titlebar">
					<xsl:call-template name="titlebar"/>
				</td>
			</tr>
			<tr>
				<td id="commandbar" class="commandbar">
					<a id="up" class="command" href="#" onclick="history.back()">back</a>
					<a class="command" href="{$web}/cat/?repo={@repo}&amp;target={@target}&amp;rev={@rev}&amp;open=1">download this file</a>
				</td>
			</tr>
			<tr>
				<td id="workarea" class="workarea">
					<h2>
						<span class="filename">
							<xsl:value-of select="@target"/>
						</span>
						<xsl:value-of select="$spacer"/>
						<span class="revision">
							<xsl:text>version </xsl:text>
							<xsl:value-of select="@rev"/>
						</span>
					</h2>
					<xsl:apply-templates select="*"/>
				</td>
			</tr>
			<tr>
				<td id="footer" class="footer">
					<xsl:value-of select="$spacer"/>
				</td>
			</tr>
		</table>
	</xsl:template>
	<!-- ====== error node, deprecated (remove when login.inc.php svnerror is not used anymore) ======== -->
	<xsl:template match="error">
		<xsl:param name="possible-cause"/>
		<h2 class="error">An error has occured</h2>
		<p>
			<xsl:text>Code </xsl:text>
			<xsl:value-of select="@code"/>
			<xsl:value-of select="$spacer"/>
			<xsl:value-of select="$possible-cause"/>
		</p>
		<xsl:apply-templates select="*"/>
		<!-- finally copy all text contents because there might have been unstructured output before the error occured -->
		<pre>
			<xsl:value-of select="/"/>
		</pre>
	</xsl:template>
	<xsl:template match="output">
		<p><xsl:value-of select="@line"/></p>
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
