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
	<xsl:param name="title">repos: history of </xsl:param>
	<!-- root url for webapp resources, not using relative urls -->
	<xsl:param name="web" select="/log/@web"/>
	<xsl:param name="static" select="$web"/>
	<!-- static contents urls, set to /themes/any/?u= for automatic theme selection -->
	<xsl:param name="cssUrl"><xsl:value-of select="$static"/>style/</xsl:param>
	<!-- when spacer space can't be avoided -->
	<xsl:param name="spacer" select="' &#160; '"/>
	<!-- document skeleton -->
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>
					<xsl:value-of select="$title"/>
					<xsl:value-of select="log/@target"/>
				</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<!-- if search crawlers has access, contents should not be cached -->
				<meta name="robots" content="noarchive"/>
				<link rel="shortcut icon" href="/favicon.ico"/>
				<!-- repos metadata -->
				<meta name="repos-service" content="open/log/" />
				<meta name="repos-repository" content="{/log/@repo}" />
				<meta name="repos-target" content="{/log/@target}" />
				<xsl:if test="/log/@base">
					<meta name="repos-base" content="{/log/@base}"/>
				</xsl:if>
				<!-- default stylesheet -->
				<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}global.css"/>
				<link title="repos" rel="stylesheet" type="text/css" href="{$cssUrl}log/log.css"/>
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
			<xsl:value-of select="substring(@target,0,string-length(@target)-string-length(@file)+1)"/>
		</xsl:param>
		<div id="commandbar">
		<img id="logo" src="{$static}style/logo/repos1.png" border="0" align="right" width="72" height="18"/>
		<a id="repository" href="{$repourl}">return to repository</a>
		<!-- can't really apply revision number to details link because log is specified using fromrev and torev
		Omitting this button because there should be one for each revision in the list.
		<a id="view" href="../?target={@target}{$basep}&amp;base={@base}">details</a>
		 -->
		</div>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="contents">
		<h1>History of <a class="folder" href="{@repo}{@target}"><xsl:value-of select="@name"/></a></h1>
		<xsl:apply-templates select="logentry"/>
		<xsl:if test="@limit">
			<xsl:call-template name="limit">
				<xsl:with-param name="url">
					<xsl:value-of select="'?target='"/>
					<xsl:value-of select="@target"/>
					<xsl:if test="@base">
						<xsl:value-of select="'&amp;base='"/>
						<xsl:value-of select="@base"/>
					</xsl:if>
				</xsl:with-param>
				<xsl:with-param name="size" select="@limit"/>
				<xsl:with-param name="next" select="@limitrev"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<!-- extra info and logos -->
	<xsl:template name="footer">
		<div id="footer">
		<span id="resourceversion" class="versiondisplay" style="display:none">repos.se log stylesheet 1.3 $Rev$</span>
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
			<div id="logentry_actions{@revision}" class="actions"></div>
		</div>
	</xsl:template>
	<xsl:template match="paths">
		<xsl:param name="fromrev"/>
		<xsl:apply-templates select="path">
			<xsl:with-param name="fromrev" select="$fromrev"/>
			<xsl:sort select="." order="ascending"/>
		</xsl:apply-templates>
	</xsl:template>
	<xsl:template match="paths/path">
		<xsl:param name="fromrev"/>
		<xsl:param name="pathid">
			<xsl:call-template name="getFileID">
				<xsl:with-param name="filename" select="."/>
			</xsl:call-template>
			<xsl:value-of select="'-'"/>
			<xsl:value-of select="../../@revision"/>
			<xsl:value-of select="@action"/>
		</xsl:param>
		<xsl:param name="target">
			<xsl:call-template name="getHref">
				<xsl:with-param name="href" select="."/>
			</xsl:call-template>
		</xsl:param>
		<xsl:param name="basep">
			<!-- unlike mod_dav_svn repos-web returns base only in parentpath setups -->
			<xsl:if test="/log/@base">
				<xsl:text>&#38;base=</xsl:text>
				<xsl:value-of select="/log/@base"/>
			</xsl:if>
		</xsl:param>
		<xsl:param name="defaultaction">
			<xsl:text>open/</xsl:text>
			<xsl:if test="@kind='file'">open/</xsl:if>
		</xsl:param>
		<div class="row log-{@action}">
			<xsl:if test="@action='A' or @action='R'">
				<a id="open:{$pathid}" class="folder" title="Added {.}" href="{$web}{$defaultaction}?target={$target}{$basep}&amp;rev={../../@revision}">
					<xsl:value-of select="."/>
				</a>
				<xsl:value-of select="$spacer"/>
				<xsl:if test="not(@copyfrom-path)">
					<a id="view:{$pathid}" class="action" href="{$web}open/?target={$target}{$basep}&amp;rev={../../@revision}&amp;action={@action}">details</a>
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
					<a id="view:{$pathid}" class="action" href="{$web}open/?target={@copyfrom-path}{$basep}&amp;rev={@copyfrom-rev}&amp;action={@action}">details</a>
				</xsl:if>
			</xsl:if>
			<xsl:if test="@action='D'">
				<span class="path" title="Deleted {.}, so it only exists in versions prior to {../../@revision}.">
					<xsl:value-of select="."/>
				</span>
				<xsl:value-of select="$spacer"/>
				<a id="view:{$pathid}" class="action" href="{$web}open/?target={$target}{$basep}&amp;rev={$fromrev}&amp;action={@action}">details</a>
			</xsl:if>
			<xsl:if test="@action='M'">
				<a id="open:{$pathid}" class="file" title="Modified {.}" href="{$web}{$defaultaction}?target={.}{$basep}&amp;rev={../../@revision}">
					<xsl:value-of select="."/>
				</a>
				<xsl:value-of select="$spacer"/>
				<a id="view:{$pathid}" class="action" href="{$web}open/?target={$target}{$basep}&amp;rev={../../@revision}&amp;fromrev={$fromrev}&amp;action={@action}">details</a>
				<a id="diff:{$pathid}" class="action" href="{$web}open/diff/?target={$target}{$basep}&amp;rev={../../@revision}&amp;fromrev={$fromrev}">diff</a>
			</xsl:if>
		</div>
	</xsl:template>
	<!-- links to browse log pages -->
	<xsl:template name="limit">
		<xsl:param name="url"/><!-- should be target maybe -->
		<xsl:param name="size"/>
		<xsl:param name="next"/>
		<p>
			<span>Limited to <xsl:value-of select="$size"/> entries.</span>
			<xsl:text>&#160;</xsl:text>
			<a id="next" href="{$url}&amp;torev={$next}&amp;limit={$size}">Show next page.</a>
		</p>
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
		<xsl:value-of select="translate($filename,'%/()@&amp;+=,~$! ','_____________')"/>
	</xsl:template>
	<!-- Escape query string metacharacters. Impossible for the browser to know if they should be escaped. -->
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
