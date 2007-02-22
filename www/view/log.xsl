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
	<xsl:output method="html" encoding="UTF-8" omit-xml-declaration="no" indent="no"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
	<!-- root url for webapp resources -->
	<xsl:param name="web" select="/log/@web"/>
	<xsl:param name="static" select="/log/@static"/>
	<!-- static contents urls, set to /themes/any/?u= for automatic theme selection -->
	<xsl:param name="cssUrl"><xsl:value-of select="$static"/>style/</xsl:param>
	<xsl:param name="cssUrl-pe"><xsl:value-of select="$static"/>themes/pe/style/</xsl:param>
	<!-- when spacer space can't be avoided -->
	<xsl:param name="spacer" select="' &#160; '"/>
	<!-- document skeleton -->
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>
					<xsl:text>repos.se: history of </xsl:text>
					<xsl:value-of select="log/@path"/>
				</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<!-- if search crawlers has access, contents should not be cached -->
				<meta name="robots" content="noarchive"/>
				<link rel="shortcut icon" href="/favicon.ico"/>
				<!-- default stylesheet -->
				<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}global.css"/>
				<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}log/log.css"/>
				<!-- pe stylesheet -->
				<link title="pe" rel="alternate stylesheet" type="text/css" href="{$cssUrl-pe}global.css"/>
				<link title="pe" rel="alternate stylesheet" type="text/css" href="{$cssUrl-pe}log/log.css"/>
				<!-- install the repos script bundle -->
				<script type="text/javascript" src="{$static}scripts/head.js"></script>
			</head>
			<body class="log xml">
				<xsl:apply-templates select="log"/>
			</body>
		</html>
	</xsl:template>
	<xsl:template match="svn">
		<xsl:apply-templates select="index"/>
	</xsl:template>
	<!-- body contents -->
	<xsl:template match="log">
		<xsl:call-template name="commandbar"/>
		<xsl:call-template name="contents"/>
		<xsl:call-template name="footer"/>
	</xsl:template>
	<!-- toolbar, directory actions -->
	<xsl:template name="commandbar">
		<xsl:param name="repourl">
			<xsl:value-of select="@repo"/>
			<xsl:value-of select="substring(@path,0,string-length(@path)-string-length(@file)+1)"/>
		</xsl:param>
		<div id="commandbar">
		<img src="{$static}style/logo/repos1.png" border="0" align="right" width="72" height="18"/>
		<a id="repository" href="{$repourl}">return to repository</a>
		</div>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="contents">
		<h1>History of <span id="fullpath" class="path"><xsl:value-of select="@path"/></span></h1>
		<xsl:apply-templates select="logentry"/>
		<xsl:if test="@limit">
			<xsl:call-template name="limit">
				<xsl:with-param name="url">?target=<xsl:value-of select="@path"/></xsl:with-param>
				<xsl:with-param name="size" select="@limit"/>
				<xsl:with-param name="next" select="@limitrev"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<!-- extra info and logos -->
	<xsl:template name="footer">
		<div id="footer">
		<span id="resourceversion" class="versiondisplay" style="display:none">repos.se log stylesheet $URL$ $Rev$</span>
		<span id="badges">
		</span>
		</div>
	</xsl:template>
	<!--
	========= svn log xml formatting ==========
	-->
	<!-- text strings -->
	<xsl:param name="show-diff">Show changes (differeces from previous revision)</xsl:param>
	<xsl:param name="undo">Reverse the changes made from previous revision to this one</xsl:param>
	<!-- layout -->
	<xsl:template match="logentry">
		<xsl:param name="n" select="position() - 1"/>
		<div id="rev{@revision}" class="logentry n{$n mod 4}">
			<h3>
				<span class="revision" title="the changeset number (version number)">
					<xsl:value-of select="@revision"/>
				</span>
				<span id="author:{@revision}" class="username" title="author">
					<xsl:value-of select="author"/>
				</span>
				<xsl:value-of select="$spacer"/>
				<span id="datetime:{@revision}" class="datetime" title="date and time of the commit">
					<xsl:value-of select="date"/>
				</span>
				<xsl:value-of select="$spacer"/>
			</h3>
			<xsl:if test="string-length(msg) > 0">
				<div id="message:{@revision}" class="message" title="Log message">
						<xsl:call-template name="linebreak">
							<xsl:with-param name="text" select="msg"/>
						</xsl:call-template>
				</div>
			</xsl:if>
			<xsl:apply-templates select="paths">
				<xsl:with-param name="fromrev" select="following-sibling::*[1]/@revision"/>
			</xsl:apply-templates>
		</div>
	</xsl:template>
	<xsl:template match="paths">
		<xsl:param name="fromrev"/>
		<xsl:apply-templates select="path">
			<xsl:with-param name="fromrev" select="$fromrev"/>
		</xsl:apply-templates>
	</xsl:template>
	<xsl:template match="paths/path">
		<xsl:param name="fromrev"/>
		<xsl:param name="pathid">
			<xsl:call-template name="getFileID">
				<xsl:with-param name="filename" select="."/>
			</xsl:call-template>
			<xsl:value-of select="../../@revision"/>
			<xsl:value-of select="@action"/>
		</xsl:param>
		<div class="row log-{@action}">
			<xsl:if test="@action='A'">
				<a id="open:{$pathid}" class="path file" title="Added {.}" href="{$web}open/open/?target={.}&amp;rev={../../@revision}">
					<xsl:value-of select="."/>
				</a>
				<xsl:value-of select="$spacer"/>
				<xsl:if test="not(@copyfrom-path)">
					<a id="view:{$pathid}" class="action" href="{$web}open/?target={.}&amp;rev={../../@revision}&amp;action={@action}">view</a>
				</xsl:if>
				<xsl:if test="@copyfrom-path">
					<span class="copied" title="Copied from {@copyfrom-path} version {@copyfrom-rev}">
						<span class="path">
							<xsl:value-of select="@copyfrom-path"/>&#160;</span>
						<span class="revision">
							<xsl:value-of select="@copyfrom-rev"/>
						</span>
					</span>
					<xsl:value-of select="$spacer"/>
					<a id="view:{$pathid}" class="action" href="{$web}open/?target={@copyfrom-path}&amp;rev={@copyfrom-rev}&amp;action={@action}">view</a>
				</xsl:if>
			</xsl:if>
			<xsl:if test="@action='D'">
				<span class="path" title="Deleted {.}, so it only exists in versions prior to {../../@revision}.">
					<xsl:value-of select="."/>
				</span>
				<xsl:value-of select="$spacer"/>
				<a id="view:{$pathid}" class="action" href="{$web}open/?target={.}&amp;rev={$fromrev}&amp;action={@action}">view</a>
			</xsl:if>
			<xsl:if test="@action='M'">
				<a id="open:{$pathid}" class="path file" title="Modified {.}" href="{$web}open/open/?target={.}&amp;rev={../../@revision}">
					<xsl:value-of select="."/>
				</a>
				<xsl:value-of select="$spacer"/>
				<a id="view:{$pathid}" class="action" href="{$web}open/?target={.}&amp;rev={../../@revision}&amp;fromrev={$fromrev}&amp;action={@action}">view</a>
				<a id="diff:{$pathid}" class="action" href="{$web}open/diff/?target={.}&amp;rev={../../@revision}&amp;fromrev={$fromrev}">diff</a>
			</xsl:if>
		</div>
	</xsl:template>
	<!-- links to browse log pages -->
	<xsl:template name="limit">
		<xsl:param name="url"/><!-- should be target maybe -->
		<xsl:param name="size"/>
		<xsl:param name="next"/>
		<p>Limited to <xsl:value-of select="$size"/> entries. <a id="next" href="{$url}&amp;torev={$next}&amp;limit={$size}">Show next page.</a></p>
	</xsl:template>
	<!-- links to select revisions -->
	<xsl:template name="limitrev">
		<!-- TODO
		  <label for="fromrev">from revision</label><input id="fromrev" type="text" name="fromrev" />
		  <label for="torev">to newer revision</label><input id="torev" type="text" name="torev" />
		  <input id="submit" type="submit" name="submit" value="Display" /> -->
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
	<!-- make valid (and probably unique) HTML id for log entry, containing [A-Za-z0-9] and [-_.] -->
	<!-- the log xml has no urlencoded values, so it will be slightly different than repository -->
	<xsl:template name="getFileID">
		<xsl:param name="filename" select="@href"/>
		<xsl:value-of select="translate($filename,'%/()@&amp; ','_______')"/>
	</xsl:template>
</xsl:stylesheet>
