<?xml version="1.0"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml"
    version="1.0">
  <xsl:output method="xml" indent="no"/>

  <!--
      ==== repos.se: Subversion directory listing layout ====
      To be set as SVNIndexXSLT in repository conf.
      Used at all directory levels, so urls must be absolute.
      (c) Staffan Olsson
   -->
  <!-- why not generate user-tailored xslt from a .jwa url in Svnindex? -->
  <!-- add parameter rurl=".." when testing offline -->
  <!-- status images like 'locked' could be generated on the fly -->

  <!-- display name of the repository -->
  <xsl:param name="repoName">www.repos.se/repos</xsl:param>
  <!-- repository URL, use path that works for user ('localhost' could break redirects) -->
  <xsl:param name="repoUrl">http://<xsl:value-of select="$repoName"/></xsl:param>
  <!-- repos webapp URL (root), does not end with slash -->
  <xsl:param name="rurl">http://www.repos.se</xsl:param>
  <!-- current theme, for example '/theme', empty for root theme -->
  <xsl:param name="theme"></xsl:param>
  <!-- static contents urls -->
  <xsl:param name="cssUrl"><xsl:value-of select="$rurl"/><xsl:value-of select="$theme"/>/css</xsl:param>
  <xsl:param name="iconsUrl"><xsl:value-of select="$rurl"/><xsl:value-of select="$theme"/>/icons</xsl:param>
  <xsl:param name="buttonsUrl"><xsl:value-of select="$rurl"/><xsl:value-of select="$theme"/>/buttons</xsl:param>
  <!-- avaliable icons -->
  <xsl:param name="icons">._folder._file.ai.bmp.xhm.doc.exe.gif.gz.htm.html.ics.jar.java.jpg.log.mpg.pdf.php.png.ps.psd.qt.sh.sit.sxw.tif.tmp.txt.vcf.xls.zip</xsl:param>
  <!-- filetype for which there is a thumbnail generator -->
  <xsl:param name="thumbs"></xsl:param>
  <!-- icon dimensions -->
  <xsl:param name="iconSize">22</xsl:param>
  <xsl:param name="iconVspace">1</xsl:param>
  <xsl:param name="iconHspace">10</xsl:param>

  <!-- thumbnail generators -->
  <xsl:param name="thumbUrl"><xsl:value-of select="$rurl"/>/phpThumb/phpThumbq.php?src=</xsl:param>
  
  <!-- include  repos.se shared templates -->
  <!-- <xsl:include href=""/> -->

  <!-- local templates -->
  <xsl:template match="*"/>

  <!-- document skeleton -->
  <xsl:template match="svn">
    <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
        <title>
          <xsl:text>repos.se </xsl:text>
          <xsl:value-of select="index/@path"/>
        </title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!-- don't cache -->
        <meta http-equiv="Pragma" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<!-- if google may index the repository (googlebot.com has access), contents should not be cached -->
		<meta name="robots" content="noarchive" />
		<!-- default stylesheet -->
        <link rel="stylesheet" type="text/css" href="{$cssUrl}/repos-standard.css"/>
      </head>
      <body>
        <xsl:apply-templates/>
      </body>
    </html>
  </xsl:template>
  
  <!-- body contents -->
  <xsl:template match="index">
      <!-- using table instead of divs now -->
    <table class="svnlayout">
        <tr><td id="titlebar" class="titlebar">
      <xsl:call-template name="titlebar-list"/>
        </td></tr><tr><td id="commandbar" class="commandbar">
      <xsl:call-template name="commandbar"/>
        </td></tr><tr><td id="workarea" class="workarea">          
      <xsl:call-template name="workarea-list"/>
        </td></tr><tr><td id="footer" class="footer">
      <xsl:call-template name="footer"/>
        </td></tr>
    </table>
  </xsl:template>
  
  <!-- header with path information -->
  <xsl:template name="titlebar-box">
      	<table width="100%" border="0" cellpadding="0" cellspacing="5">
		   <tr>
			 <td>repo: <xsl:value-of select="$repoName"/></td>
			 <td><div align="right">version: <xsl:value-of select="@rev"/></div></td>
			 <td>&#160;</td>
			 <td rowspan="2" align="right"><img src="{$rurl}/logo/repos2.png" width="142" height="38" /></td>
		   </tr>
		   <tr>
			 <td colspan="2"><xsl:value-of select="@path"/></td>
			 <td>&#160;</td>
		   </tr>
		 </table>
  </xsl:template>
  
  <xsl:template name="titlebar-list">
    <a href="http://www.repos.se/"><img src="{$rurl}/logo/repos1.png" border="0" align="right" width="72" height="18" /></a>
    <xsl:value-of select="$repoName"/> &#160; <xsl:value-of select="@path"/> &#160; version <xsl:value-of select="@rev"/>
  </xsl:template>
  
  <!-- directory actions, stuff like that -->
  <xsl:template name="commandbar">
    <xsl:if test="/svn/index/updir">
        <a class="command" href="../"><xsl:call-template name="showicon"><xsl:with-param name="filetype" select="'_parent'"/></xsl:call-template>up</a>   
    </xsl:if>
    <xsl:if test="not(/svn/index/updir)">
        <span class="command"><xsl:call-template name="showicon"><xsl:with-param name="filetype" select="'_parent'"/></xsl:call-template>up</span>   
    </xsl:if>
    <span class="command"><xsl:call-template name="showicon"><xsl:with-param name="filetype" select="'_newfolder'"/></xsl:call-template>new folder</span> 
    <span class="command"><xsl:call-template name="showicon"><xsl:with-param name="filetype" select="'_upload'"/></xsl:call-template>upload</span>
    <span class="command"><xsl:call-template name="showicon"><xsl:with-param name="filetype" select="'_windowsfolder'"/></xsl:call-template>open in Windows</span>
    <span class="command"><xsl:call-template name="showicon"><xsl:with-param name="filetype" select="'_tortoisefolder'"/></xsl:call-template>check out</span>
  </xsl:template>

  <!-- directory listing -->
  <xsl:template name="workarea-list">
      <xsl:apply-templates select="dir" mode="list">
          <xsl:sort select="@name"/>
      </xsl:apply-templates>
      <xsl:apply-templates select="file" mode="list">
          <xsl:sort select="@name"/>
      </xsl:apply-templates>
  </xsl:template>
  
  <xsl:template name="workarea-box">
    <table width="100%" border="0" cellspacing="0">
        <tr>
            <td>
              <xsl:apply-templates select="dir" mode="box"/>
              <xsl:apply-templates select="file" mode="box"/>
            </td>
          </tr>
    </table> 
  </xsl:template>

  <!-- extra info, links to top -->
  <xsl:template name="footer">
      <xsl:text>Powered by </xsl:text>
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="../@href"/>
        </xsl:attribute>
        <xsl:text>Subversion</xsl:text>
      </xsl:element>
      <xsl:text> </xsl:text>
      <xsl:value-of select="../@version"/>
  </xsl:template>

  <!-- generate directory -->
  <xsl:template match="dir" mode="box">
    <table class="file" border="0">
      <tr>
        <td rowspan="2" width="36"><img src="{$rurl}/icons/_folder.png" width="36" height="36" /></td>
        <td><a class="filename" href="{@href}"><xsl:value-of select="@name"/></a></td>
      </tr>
      <tr>
        <td><small>[info] [delete]</small></td>
      </tr>
    </table>
  </xsl:template>
  
  <xsl:template match="dir" mode="list">
    <p>
        <a class="filename" href="{@href}">
        <xsl:call-template name="getIcon">
            <xsl:with-param name="filetype" select="'_folder'"/>
        </xsl:call-template>
        <xsl:value-of select="@name"/>
        </a>
        <span class="action">info</span>
        <span class="action">delete</span>
    </p> 
  </xsl:template>
  
  <!-- generate file -->
  <!-- note the fixed icon width -->
  <xsl:template match="file" mode="box">
    <table class="file" border="0">
      <tr>
        <td rowspan="2" width="36"><xsl:call-template name="getIcon"/></td>
        <td><a class="filename" href="{@href}"><xsl:value-of select="@name"/></a></td>
      </tr>
      <tr>
        <td><small>[info] <xsl:call-template name="getReposLink"><xsl:with-param name="text">open</xsl:with-param></xsl:call-template>] [delete] [upload changes] [locked?]</small></td>
      </tr>
    </table>
  </xsl:template>
  
  <xsl:template match="file" mode="list">
    <p>
        <a class="filename" href="{@href}"><xsl:call-template name="getIcon"/><xsl:value-of select="@name"/></a>
        <span class="action">info</span>
        <xsl:call-template name="getReposLink"><xsl:with-param name="text">open</xsl:with-param></xsl:call-template>
        <span class="action">delete</span>
        <span class="action">upload changes</span> 
        <span class="action">lock</span>
    </p> 
  </xsl:template>
  
  <!-- display link to open resource in Repos -->
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
          <!-- repo not ending with '/' -->
          <xsl:value-of select="'?repo='"/><xsl:value-of select="$repoUrl"/>
          <!-- path not ending with '/' -->
          <xsl:value-of select="'&amp;path='"/><xsl:value-of select="$path"/>
          <!-- filename -->
          <xsl:value-of select="'&amp;file='"/><xsl:value-of select="$file"/>
        </xsl:attribute>
        <xsl:value-of select="$text"/>
      </xsl:element>
  </xsl:template>
  
  <!-- generate icon based on filetype and settings -->
  <xsl:template name="getIcon">
  	<!-- input: lowercase file extension -->
  	<xsl:param name="filetype"><xsl:call-template name="getFiletype"/></xsl:param>
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
      <xsl:param name="filetype"><xsl:call-template name="getFiletype"/></xsl:param>
      <img src="{$rurl}/icons/{$filetype}.png" border="0" align="absmiddle" width="{$iconSize}" height="{$iconSize}" hspace="{$iconHspace}" vspace="{$iconVspace}"/>
  </xsl:template>
  
  <!-- display thumbnail as icon -->
  <xsl:template name="thumbnail">
      <img src="{$thumbUrl}{$repoUrl}{../@path}/{@href}" vspace="5" hspace="5" border="0"/>
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

</xsl:stylesheet>
