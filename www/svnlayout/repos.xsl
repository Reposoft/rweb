<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml" version="1.0">
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
	<xsl:param name="rurl">https://www.repos.se</xsl:param>
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
	<!-- avaliable icons -->
	<xsl:param name="icons">._folder._file.ai.bmp.xhm.doc.exe.gif.gz.htm.html.ics.jar.java.jpg.log.mpg.pdf.php.png.ps.psd.qt.sh.sit.sxw.tif.tmp.txt.vcf.xls.zip</xsl:param>
	<!-- filetype for which there is a thumbnail generator -->
	<xsl:param name="thumbs">.jpeg</xsl:param>
	<!-- filetype for which there is an integrated viewer -->
	<xsl:param name="views">.doc.ics.gan</xsl:param>
	<!-- icon dimensions -->
	<xsl:param name="iconSize">22</xsl:param>
	<xsl:param name="iconVspace">1</xsl:param>
	<xsl:param name="iconHspace">10</xsl:param>
	<!-- more layout definitions -->
	<xsl:param name="spacer" select="' &#160; '"/>
	<!-- thumbnail generators -->
	<xsl:param name="thumbUrl">
		<xsl:value-of select="$rurl"/>/phpThumb/phpThumbq.php?src=</xsl:param>
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
			</head>
			<body>
				<!-- supported contents -->
				<xsl:apply-templates select="svn"/>
				<xsl:apply-templates select="log"/>
				<xsl:apply-templates select="diff"/>
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
		<xsl:if test="@repo"><xsl:value-of select="@repo"/><xsl:value-of select="$spacer"/></xsl:if>
		<span class="path"><xsl:value-of select="@path"/></span>
		<xsl:value-of select="$spacer"/>
		<xsl:if test="@rev">revision <span class="revision"><xsl:value-of select="@rev"/></span></xsl:if>
	</xsl:template>
	<!-- directory actions, stuff like that -->
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
		<span class="command">
			<xsl:call-template name="showicon">
				<xsl:with-param name="filetype" select="'_newfolder'"/>
			</xsl:call-template>new folder</span>
		<span class="command">
			<xsl:call-template name="showicon">
				<xsl:with-param name="filetype" select="'_upload'"/>
			</xsl:call-template>upload</span>
		<span class="command">
			<xsl:call-template name="showicon">
				<xsl:with-param name="filetype" select="'_windowsfolder'"/>
			</xsl:call-template>open in Windows</span>
		<span class="command">
			<xsl:call-template name="showicon">
				<xsl:with-param name="filetype" select="'_tortoisefolder'"/>
			</xsl:call-template>check out</span>
		<a class="command" href="{$rurl}/log/?path={@path}">
			<xsl:call-template name="showicon">
				<xsl:with-param name="filetype" select="'_log'"/>
			</xsl:call-template>show log</a>
		<a class="command" href="{$rurl}/logout.php">
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
		<xsl:text>Powered by </xsl:text>
		<xsl:element name="a">
			<xsl:attribute name="href">
				<xsl:value-of select="../@href"/>
			</xsl:attribute>
			<xsl:attribute name="target">
				<xsl:value-of select="'_blank'"/>
			</xsl:attribute>
			<xsl:text>Subversion</xsl:text>
		</xsl:element>
		<xsl:text>&#160;</xsl:text>
		<xsl:value-of select="../@version"/>
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
			<span class="action">rename</span>
			<span class="action">copy</span>
			<span class="action">delete</span>
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
			<xsl:call-template name="getReposLink">
				<xsl:with-param name="text">open</xsl:with-param>
			</xsl:call-template>
			<span class="action">rename</span>
			<span class="action">copy</span>
			<span class="action">delete</span>
			<span class="action">lock</span>
			<span class="action">upload changes</span>
		</p>
	</xsl:template>
	<!-- display link to open the resource in Repos -->
	<xsl:template name="getReposLink">
		<xsl:param name="name" select="@name"/>
		<xsl:param name="path" select="../@path"/>
		<xsl:param name="text" select="@name"/>
		<xsl:param name="file" select="@href"/>
		<xsl:element name="a">
			<xsl:attribute name="class">
				<xsl:value-of select="'action'"/>
			</xsl:attribute>
			<xsl:attribute name="href">
				<xsl:value-of select="$rurl"/>
				<xsl:value-of select="'/'"/>
				<!-- ...file.type.jwa mapping to controller -->
				<xsl:value-of select="$name"/>
				<xsl:value-of select="'.jwa'"/>
				<!-- path not ending with '/' -->
				<xsl:value-of select="'&amp;path='"/>
				<xsl:value-of select="$path"/>
				<!-- filename -->
				<xsl:value-of select="'&amp;file='"/>
				<xsl:value-of select="$file"/>
			</xsl:attribute>
			<xsl:value-of select="$text"/>
		</xsl:element>
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
		<img src="{$iconsUrl}/{$filetype}.png" border="0" align="absmiddle" width="{$iconSize}"
			height="{$iconSize}" hspace="{$iconHspace}" vspace="{$iconVspace}"/>
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
		<table class="svnlayout">
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
					<xsl:value-of select="msg"/>
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
			<xsl:if test="not(@action='M')">
				<span class="action"><xsl:value-of select="@action"/></span>
			</xsl:if>
			<xsl:if test="@action='M'">
				<a title="{$show-diff}" class="action" href="{$rurl}/diff/?repo={../../../@repo}&amp;path={.}&amp;revto={../../@revision}&amp;revfrom={$revfrom}"><xsl:value-of select="@action"/></a>
			</xsl:if>
			<xsl:value-of select="$spacer"/>
			<span class="filename">
				<xsl:value-of select="."/>
			</span>
		</p>
	</xsl:template>
	<!--
	========= svn diff formatting ==========
	-->
	<xsl:template match="diff">
		<table class="svnlayout">
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
							<xsl:value-of select="@path"/>
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
					<code><pre>
						<xsl:value-of select="."/>
					</pre></code>
				</td>
			</tr>
			<tr>
				<td id="footer" class="footer">
					<xsl:value-of select="$spacer"/>
				</td>
			</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>
