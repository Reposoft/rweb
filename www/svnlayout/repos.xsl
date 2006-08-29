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
	<xsl:param name="theme" select="'/themes/simple'"/>
	<!-- static contents urls -->
	<xsl:param name="cssUrl">
		<xsl:value-of select="$rurl"/>
		<xsl:value-of select="$theme"/>/css</xsl:param>
	<xsl:param name="iconsUrl">
		<xsl:value-of select="$rurl"/>
		<xsl:value-of select="$theme"/>/icons</xsl:param>
	<xsl:param name="buttonsUrl">
		<xsl:value-of select="$rurl"/>
		<xsl:value-of select="$theme"/>/buttons</xsl:param>
	<!-- start url for simple WebDAV-like manipulation of repository, empty if not available -->
	<xsl:param name="editUrl">
		<xsl:value-of select="$rurl"/>/edit</xsl:param>
	<!-- avaliable icons -->
	<xsl:param name="icons">._folder._file.ai.bmp.xhm.doc.exe.gif.gz.htm.html.ics.jar.java.jpg.log.mpg.pdf.php.png.ps.psd.qt.sh.sit.sxw.tif.tmp.txt.vcf.xls.zip</xsl:param>
	<!-- filetype for which there is a thumbnail generator -->
	<xsl:param name="thumbs">.jpeg</xsl:param>
	<!-- filetype for which there is an integrated viewer -->
	<xsl:param name="views">.html.ics</xsl:param>
	<!-- icon dimensions -->
	<xsl:param name="iconSize">22</xsl:param>
	<xsl:param name="miniIconSize">16</xsl:param>
	<xsl:param name="iconVspace">1</xsl:param>
	<xsl:param name="iconHspace">10</xsl:param>
	<!-- html layout definitions -->
	<xsl:param name="spacer" select="' &#160; '"/>
	<!-- thumbnail generators -->
	<xsl:param name="thumbUrl">
		<xsl:value-of select="$rurl"/>/phpthumb/phpThumbq.php?src=</xsl:param>
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
				<!-- don't cache -->
				<meta http-equiv="Pragma" content="no-cache"/>
				<meta http-equiv="expires" content="0"/>
				<!-- if google may index the repository (googlebot.com has access), contents should not be cached -->
				<meta name="robots" content="noarchive"/>
				<!-- default stylesheet -->
				<link rel="stylesheet" type="text/css" href="{$cssUrl}/repos-standard.css"/>
				<link rel="shortcut icon" href="http://www.repos.se/favicon.ico"/>
				<!-- install repos-quay, this row and an icon in the footer -->
				<script type="text/javascript" src="/quay/repos-quay.js"></script>
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
				<td id="titlebar" class="titlebar">
					<xsl:call-template name="titlebar"/>
				</td>
			</tr>
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
	<!-- header with path information -->
	<xsl:template name="titlebar">
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
		<xsl:if test="@rev">revision <span class="revision">
				<xsl:value-of select="@rev"/>
			</span>
		</xsl:if>
	</xsl:template>
	<!-- toolbar, directory actions -->
	<xsl:template name="commandbar">
		<xsl:if test="/svn/index/updir">
			<a class="command" href="../">
				<xsl:call-template name="showicon">
					<xsl:with-param name="filetype" select="'_parent'"/>
				</xsl:call-template>up</a>
		</xsl:if>
		<xsl:if test="not(/svn/index/updir)">
			<span class="command">
				<xsl:call-template name="showicon">
					<xsl:with-param name="filetype" select="'_parent'"/>
				</xsl:call-template>up</span>
		</xsl:if>
		<xsl:if test="$editUrl">
			<a class="command" href="{$editUrl}/?action=mkdir&amp;path={@path}">
				<xsl:call-template name="showicon">
					<xsl:with-param name="filetype" select="'_newfolder'"/>
				</xsl:call-template>new folder</a>
			<a class="command" href="{$rurl}/upload/?path={@path}">
				<xsl:call-template name="showicon">
					<xsl:with-param name="filetype" select="'_upload'"/>
				</xsl:call-template>upload</a>
		</xsl:if>
		<a class="command" href="{$rurl}/tutorials/?show=networkfolder">
			<xsl:call-template name="showicon">
				<xsl:with-param name="filetype" select="'_windowsfolder'"/>
			</xsl:call-template>open folder</a>
		<!--<a class="command" href="{$rurl}/tutorials/?show=checkout">-->
		<a class="command" href="{$rurl}/tutorials/?show=checkout">
			<xsl:call-template name="showicon">
				<xsl:with-param name="filetype" select="'_tortoisefolder'"/>
			</xsl:call-template>check out</a>
		<a class="command" href="{$rurl}/log/?path={@path}">
			<xsl:call-template name="showicon">
				<xsl:with-param name="filetype" select="'_log'"/>
			</xsl:call-template>show log</a>
		<a class="command" href="/?logout">
			<xsl:call-template name="showicon">
				<xsl:with-param name="filetype" select="'_logout'"/>
			</xsl:call-template>logout</a>
	</xsl:template>
	<!-- directory listing -->
	<xsl:template name="workarea">
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
		<span>
			<xsl:text>&#160;</xsl:text>
			<a id="quayButton"></a>
		</span>
	</xsl:template>
	<!-- generate directory -->
	<xsl:template match="dir">
		<p>
			<a class="filename" href="{@href}">
				<xsl:call-template name="getIcon">
					<xsl:with-param name="filetype" select="'_folder'"/>
				</xsl:call-template>
				<xsl:value-of select="@name"/>
			</a>
			<xsl:value-of select="$spacer"/>
			<span class="action">info</span>
			<xsl:if test="$editUrl">
				<a class="action" href="{$editUrl}/?action=rename&amp;path={../@path}/{@href}">rename</a>
				<span class="action">copy</span>
				<a class="action" href="{$editUrl}/?action=delete&amp;path={../@path}/{@href}">delete</a>
			</xsl:if>
		</p>
	</xsl:template>
	<!-- generate file -->
	<xsl:template match="file">
		<p>
			<a class="filename" href="{@href}">
				<xsl:call-template name="getIcon"/>
				<xsl:value-of select="@name"/>
			</a>
			<xsl:value-of select="$spacer"/>
			<span class="action">info</span>
			<a class="action" title="this file can be opened in Repos" href="{$rurl}/open/?path={../@path}&amp;file={@href}">open</a>
			<xsl:if test="$editUrl">
				<a class="action" href="{$editUrl}/?action=rename&amp;path={../@path}&amp;file={@href}">rename</a>
				<span class="action">copy</span>
				<a class="action" href="{$editUrl}/?action=delete&amp;path={../@path}&amp;file={@href}">delete</a>
				<span class="action">lock</span>
				<a class="action" href="{$rurl}/upload/?path={../@path}&amp;file={@href}">upload changes</a>
			</xsl:if>
		</p>
	</xsl:template>
	<!-- generate icon based on filetype and settings -->
	<xsl:template name="getIcon">
		<!-- input: lowercase file extension -->
		<xsl:param name="filetype">
			<xsl:call-template name="getFiletype"/>
		</xsl:param>
		<!-- all handled filetypes -->
		<xsl:choose>
			<!-- add thumbnail check and call here -->
			<!-- check if filetype is in the icons list -->
			<xsl:when test="contains($icons,concat('.',$filetype))">
				<xsl:call-template name="showicon">
					<xsl:with-param name="filetype" select="$filetype"/>
				</xsl:call-template>
			</xsl:when>
			<!-- default icon -->
			<xsl:otherwise>
				<xsl:call-template name="showicon">
					<xsl:with-param name="filetype" select="'_file'"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- just display filetype icon, no check -->
	<xsl:template name="showicon">
		<xsl:param name="filetype">
			<xsl:call-template name="getFiletype"/>
		</xsl:param>
		<img src="{$iconsUrl}/{$filetype}.png" border="0" align="absmiddle" width="{$iconSize}" height="{$iconSize}" hspace="{$iconHspace}" vspace="{$iconVspace}"/>
	</xsl:template>
	<!-- display thumbnail as icon -->
	<xsl:template name="thumbnail">
		<img src="{$thumbUrl}{../@path}/{@href}" vspace="5" hspace="5" border="0"/>
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
					<a class="command" href="{@repo}{@path}">
						<xsl:call-template name="showicon">
							<xsl:with-param name="filetype" select="'_parent'"/>
						</xsl:call-template>up</a>
				</td>
			</tr>
			<tr>
				<td id="workarea" class="workarea">
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
			<xsl:if test="msg">
				<p>
					<xsl:call-template name="linebreak">
						<xsl:with-param name="text" select="msg"/>
					</xsl:call-template>
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
				<xsl:value-of select="."/>
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
				<xsl:value-of select="."/>
			</xsl:if>
			<xsl:if test="@action='M'">
				<a title="{@action} - {$show-diff}" href="{$rurl}/diff/?repo={../../../@repo}&amp;target={.}&amp;revto={../../@revision}&amp;revfrom={$revfrom}">
					<xsl:call-template name="logicon">
						<xsl:with-param name="name" select="'_m'"/>
					</xsl:call-template>
				</a>
				<xsl:value-of select="."/>
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
		<img src="{$iconsUrl}/{$name}.png" border="0" align="absmiddle" width="{$miniIconSize}" height="{$miniIconSize}" hspace="{$iconHspace}" vspace="{$iconVspace}"/>
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
					<a class="command" href="#" onclick="history.back()">
						<xsl:call-template name="showicon">
							<xsl:with-param name="filetype" select="'_parent'"/>
						</xsl:call-template>back</a>
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
	<xsl:template match="plaintext">
		<pre>
			<xsl:value-of select="."/>
		</pre>
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
					<a class="command" href="#" onclick="history.back()">
						<xsl:call-template name="showicon">
							<xsl:with-param name="filetype" select="'_parent'"/>
						</xsl:call-template>back</a>
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
							<xsl:text>from revision </xsl:text>
							<xsl:value-of select="@rev"/>
						</span>
					</h2>
					<p>
						<a class="action" href="{$rurl}/cat/?repo={@repo}&amp;target={@target}&amp;rev={@rev}&amp;open=1">open old file</a>
					</p>
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
	<!-- ====== error node ======== -->
	<xsl:template match="error">
		<xsl:param name="possible-cause"/>
		<h2 class="error">An error has occured</h2>
		<p>
			<xsl:text>Code </xsl:text>
			<xsl:value-of select="@code"/>
			<xsl:value-of select="$spacer"/>
			<xsl:value-of select="$possible-cause"/>
		</p>
		<pre class="error">
			<xsl:value-of select="."/>
		</pre>
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
