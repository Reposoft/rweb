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
	<!-- root url for webapp resources -->
	<xsl:param name="web">/repos</xsl:param>
	<!-- static contents urls, set to /themes/any/?u= for automatic theme selection -->
	<xsl:param name="cssUrl"><xsl:value-of select="$web"/>/style/</xsl:param>
	<!-- when spacer space can't be avoided -->
	<xsl:param name="spacer" select="' &#160; '"/>
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
				<!-- default stylesheet -->
				<link rel="stylesheet" type="text/css" href="{$cssUrl}global.css"/>
				<link rel="stylesheet" type="text/css" href="{$cssUrl}log/log.css"/>
				<link rel="shortcut icon" href="/favicon.ico"/>
				<!-- install the repos script bundle -->
				<script type="text/javascript" src="{$web}/scripts/head.js"></script>
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
		<div class="workspace">
			<xsl:call-template name="commandbar"/>
			<xsl:call-template name="contents"/>
			<xsl:call-template name="footer"/>
		</div>
	</xsl:template>
	<!-- toolbar, directory actions -->
	<xsl:template name="commandbar">
		<div class="commandbar">
		<a id="reposbutton">
			<img src="{$web}/style/logo/repos1.png" border="0" align="right" width="72" height="18"/>
		</a>
		<a id="repository" class="command" href="{@repo}{@path}">return to repository</a>
		</div>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="contents">
		<h1>History of <span class="path"><xsl:value-of select="@path"/></span></h1>
		<div class="contents">
			<xsl:apply-templates select="error"/>
			<xsl:apply-templates select="logentry"/>
		</div>
	</xsl:template>
	<!-- extra info and logos -->
	<xsl:template name="footer">
		<div class="footer">
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
				<span class="username" title="author">
					<xsl:value-of select="author"/>
				</span>
				<xsl:value-of select="$spacer"/>
				<span class="datetime" title="date and time of the commit">
					<xsl:value-of select="date"/>
				</span>
				<xsl:value-of select="$spacer"/>
				<!-- TODO <a title="{$undo}" class="action" href="{$web}/edit/undo/?rev={@revision}">undo</a> -->
			</h3>
			<xsl:if test="string-length(msg) > 0">
				<div class="message" title="Log message">
						<xsl:call-template name="linebreak">
							<xsl:with-param name="text" select="msg"/>
						</xsl:call-template>
				</div>
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
		<div class="row log-{@action}">
			<xsl:if test="@action='A'">
				<span class="path" title="Added {.}">
					<xsl:value-of select="."/>
				</span>
				<xsl:value-of select="$spacer"/>
				<xsl:if test="@copyfrom-path">
					<span class="copied" title="Copied from {@copyfrom-path} version {@copyfrom-rev}">
						<span class="path">
							<xsl:value-of select="@copyfrom-path"/>&#160;</span>
						<span class="revision">
							<xsl:value-of select="@copyfrom-rev"/>
						</span>
					</span>
				</xsl:if>
			</xsl:if>
			<xsl:if test="@action='D'">
				<span class="path" title="Deleted {.}, so it only exists in versions prior to {../../@revision}.">
					<xsl:value-of select="."/>
				</span>
			</xsl:if>
			<xsl:if test="@action='M'">
				<span class="path" title="Modified {.}">
					<xsl:value-of select="."/>
				</span>
				<div class="actions">
					<a title="the file as it was before the change" class="action" href="{$web}/open/cat/?target={.}&amp;rev={$revfrom}">before</a>
					<a title="the file after the change" class="action" href="{$web}/open/cat/?target={.}&amp;rev={../../@revision}">after</a>
					<a title="the difference this change made" class="action" href="{$web}/open/diff/?target={.}&amp;revto={../../@revision}&amp;revfrom={$revfrom}">show diff</a>
				</div>
			</xsl:if>
		</div>
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
