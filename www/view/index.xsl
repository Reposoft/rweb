<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:output method="xml" indent="no"/>
	<!--
	  ==== repos.se: Subversion directory listing layout ====
	  To be set as SVNIndexXSLT in repository conf.
	  Used at all directory levels, so urls must be absolute.
	  (c) Staffan Olsson
	
	  Note that browser transformations only work if the
	  stylesheet is read from the same domain as the XML
   	-->
	<!-- why not generate user-tailored xslt from a .jwa url in Svnindex? -->
	<!-- add parameter rurl=".." when testing offline -->
	<!-- status images like 'locked' could be generated on the fly -->
	<!-- repos webapp URL (root), does not end with slash -->
	<xsl:param name="rurl">/repos</xsl:param><!-- absolute from server root -->
	<!--<xsl:param name="rurl">http://alto.optime.se/repos</xsl:param>-->
	<!-- current theme, for example '/theme', empty for root theme -->
	<xsl:param name="theme" select="''"/>
	<!-- static contents urls -->
	<xsl:param name="cssUrl">
		<xsl:value-of select="$rurl"/>
		<xsl:value-of select="$theme"/>/style</xsl:param>
	<!-- start url for simple WebDAV-like manipulation of repository, empty if not available -->
	<xsl:param name="editUrl">
		<xsl:value-of select="$rurl"/>/edit</xsl:param>
	<!-- html layout definitions -->
	<xsl:param name="spacer" select="' &#160; '"/>
	<!-- include  repos.se shared templates -->
	<!-- <xsl:include href=""/> -->
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
				<script type="text/javascript" src="{$rurl}/scripts/head.js"></script>
			</head>
			<body>
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
		<!-- using table instead of divs now -->
		<table class="info" width="98%" align="center">
			<tr>
				<td id="commandbar" class="commandbar">
					<xsl:call-template name="commandbar"/>
				</td>
			</tr>
			<tr>
				<td id="workarea" class="workarea">
					<xsl:call-template name="workarea"/>
				</td>
			</tr>
			<tr>
				<td id="footer" class="footer">
					<xsl:call-template name="footer"/>
				</td>
			</tr>
		</table>
	</xsl:template>
	<!-- toolbar, directory actions -->
	<xsl:template name="commandbar">
		<xsl:choose>
			<xsl:when test="/svn/index/updir and not(substring-after(/svn/index/@path, '/trunk')='')">
				<a id="up" class="command up translate" href="../">up</a>
			</xsl:when>
			<xsl:otherwise>
				<span id="up" class="command translate">up</span>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="$editUrl">
			<a id="newfolder" class="command translate" href="{$editUrl}/?action=mkdir&amp;path={@path}">new folder</a>
			<a id="upload" class="command translate" href="{$rurl}/upload/?path={@path}">upload</a>
		</xsl:if>
		<!--
		<a class="command" href="{$rurl}/tutorials/?show=networkfolder">
			<xsl:call-template name="showbutton">
				<xsl:with-param name="filetype" select="'_windowsfolder'"/>
			</xsl:call-template>open folder</a>
		<a class="command" href="{$rurl}/tutorials/?show=checkout">
			<xsl:call-template name="showbutton">
				<xsl:with-param name="filetype" select="'_tortoisefolder'"/>
			</xsl:call-template>check out</a>
		-->
		<a id="showlog" class="command translate" href="{$rurl}/log/?path={@path}">show log</a>
		<a id="logout" class="command translate" href="/?logout">logout</a>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="workarea">
		<h1>
			<a href="http://www.repos.se/">
				<img src="{$rurl}/logo/repos1.png" border="0" align="right" width="72" height="18"/>
			</a>
			<xsl:if test="@repo">
				<xsl:value-of select="@repo"/>
				<xsl:value-of select="$spacer"/>
			</xsl:if>
			<span class="path">
				<xsl:value-of select="@path"/>
			</span>
			<xsl:value-of select="$spacer"/>
			<xsl:if test="@rev">
				<span class="revision">
					<xsl:value-of select="@rev"/>
				</span>
			</xsl:if>
		</h1>
		<xsl:apply-templates select="dir">
			<xsl:sort select="@name"/>
		</xsl:apply-templates>
		<xsl:apply-templates select="file">
			<xsl:sort select="@name"/>
		</xsl:apply-templates>
	</xsl:template>
	<!-- extra info, links to top -->
	<xsl:template name="footer">
		<span>
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
	</xsl:template>
	<!-- generate directory -->
	<xsl:template match="dir">
		<p>
			<a class="folder" href="{@href}">
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
		</p>
	</xsl:template>
	<!-- generate file -->
	<xsl:template match="file">
		<xsl:param name="filetype">
			<xsl:call-template name="getFiletype"/>
		</xsl:param>
		<p>
			<a class="file file-{$filetype}" href="{@href}">
				<xsl:value-of select="@name"/>
			</a>
			<xsl:value-of select="$spacer"/>
			<div class="actions">
				<a class="action" title="this file can be opened in Repos" href="{$rurl}/open/?path={../@path}&amp;file={@href}">open</a>
				<xsl:if test="$editUrl">
					<a class="action" href="{$editUrl}/?action=rename&amp;path={../@path}&amp;file={@href}">rename</a>
					<span class="action">copy</span>
					<a class="action" href="{$editUrl}/?action=delete&amp;path={../@path}&amp;file={@href}">delete</a>
					<span class="action">lock</span>
					<a class="action" href="{$rurl}/upload/?path={../@path}&amp;file={@href}">upload changes</a>
				</xsl:if>
			</div>
		</p>
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
				<a title="{$undo}" class="action" href="{$rurl}/undo/?repo={../@repo}&amp;rev={@revision}">undo</a>
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
				<a title="{@action} - {$show-diff}" href="{$rurl}/diff/?repo={../../../@repo}&amp;target={.}&amp;revto={../../@revision}&amp;revfrom={$revfrom}">
					<xsl:call-template name="logicon">
						<xsl:with-param name="name" select="'_m'"/>
					</xsl:call-template>
				</a>
				<span class="path">
					<xsl:value-of select="."/>
				</span>
				<xsl:value-of select="$spacer"/>
				<a class="action" href="{$rurl}/cat/?repo={../../../@repo}&amp;target={.}&amp;rev={$revfrom}">before</a>
				<a class="action" href="{$rurl}/cat/?repo={../../../@repo}&amp;target={.}&amp;rev={../../@revision}">after</a>
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
					<a class="command" href="{$rurl}/cat/?repo={@repo}&amp;target={@target}&amp;rev={@rev}&amp;open=1">download this file</a>
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
