<?xml version="1.0"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml"
    version="1.0">
  <xsl:output method="xml"/>

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
  <xsl:param name="icons">.html.doc</xsl:param>
  <!-- filetype for which there is a thumbnail generator -->
  <xsl:param name="thumbs">.jpg.gif</xsl:param>

  <!-- thumbnail generators -->
  <xsl:param name="thumbUrl"><xsl:value-of select="$rurl"/>/phpThumb/phpThumbq.php?src=</xsl:param>
  
  <!-- listing layout -->
  <xsl:param name="columns">3</xsl:param>
  <xsl:param name="minRows">10</xsl:param>
  
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
        <link rel="stylesheet" type="text/css" href="{$cssUrl}/repos-standard.css"/>
      </head>
      <body>
        <xsl:apply-templates/>
      </body>
    </html>
  </xsl:template>
  
  <!-- body contents -->
  <xsl:template match="index">
    <div class="main">
      <xsl:call-template name="titlebar"/>
      <xsl:call-template name="commandbar"/>
      <xsl:call-template name="workarea"/>
      <xsl:call-template name="footer"/>
    </div>
  </xsl:template>
  
  <!-- header with path information -->
  <xsl:template name="titlebar">
      <div class="titlebar">
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
	   </div>
  </xsl:template>
  
  <!-- directory actions, stuff like that -->
  <xsl:template name="commandbar">
      <div class="commandbar">
          <xsl:call-template name="updir"/>
          <xsl:text> [new folder] [upload file] [open in windows] [checkout to my computer (tortoise)]</xsl:text>
      </div>
  </xsl:template>

  <!-- directory listing -->
  <xsl:template name="workarea">
	 <div class="workarea">
    <table width="100%" border="0" cellspacing="0">
        <tr>
            <td>
              <xsl:apply-templates select="dir" mode="box"/>
              <xsl:apply-templates select="file" mode="box"/>
            </td>
          </tr>
    </table>
    </div>  
  </xsl:template>

  <!-- extra info, links to top -->
  <xsl:template name="footer">
    <div class="footer">
      <xsl:text>Powered by </xsl:text>
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="@href"/>
        </xsl:attribute>
        <xsl:text>Subversion</xsl:text>
      </xsl:element>
      <xsl:text> </xsl:text>
      <xsl:value-of select="@version"/>
    </div>
  </xsl:template>

  <!-- show button to go up to parent folder, greyed out if there is no parent folder -->
  <xsl:template name="updir">
      <xsl:text>[</xsl:text>
      <xsl:if test="/svn/index/updir">
      <xsl:element name="a">
        <xsl:attribute name="href">..</xsl:attribute>
        <xsl:text>Parent Directory</xsl:text>
      </xsl:element>
      </xsl:if>
      <xsl:text>]</xsl:text>
  </xsl:template>

  <!-- generate directory -->
  <xsl:template match="dir" mode="box">
    <table class="file" border="0">
      <tr>
        <td rowspan="2" width="36"><img src="{$rurl}/icons/_folder.png" width="36" height="36" /></td>
        <td><xsl:call-template name="getLink"/></td>
      </tr>
      <tr>
        <td>[info] [delete]</td>
      </tr>
    </table>
  </xsl:template>
  
  <!-- generate file -->
  <!-- note the fixed icon width -->
  <xsl:template match="file" mode="box">
    <table class="file" border="0">
      <tr>
        <td rowspan="2" width="36"><xsl:call-template name="icon"></xsl:call-template></td>
        <td><xsl:call-template name="getLink"/></td>
      </tr>
      <tr>
        <td>[info] [<xsl:call-template name="getReposLink"><xsl:with-param name="text">open</xsl:with-param></xsl:call-template>] [delete]</td>
      </tr>
    </table>
  </xsl:template>
  
  <!-- display link directly to resource -->
  <xsl:template name="getLink">
      <xsl:param name="name" select="@name"/>
      <xsl:param name="href" select="@href"/>
        <xsl:element name="a">
            <xsl:attribute name="href">
                <xsl:value-of select="$href"/>
            </xsl:attribute>
            <xsl:value-of select="$name"/>
        </xsl:element>
  </xsl:template>
  
  <!-- display link to open resource -->
  <xsl:template name="getReposLink">
      <xsl:param name="name" select="@name"/>
      <xsl:param name="path" select="../@path"/>
      <xsl:param name="text" select="@name"/>
      <xsl:param name="file" select="@href"/>
      <xsl:element name="a">
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
  <xsl:template name="icon">
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
      <img src="{$rurl}/icons/{$filetype}.png" border="0" hspace="0" vspace="0"/>
  </xsl:template>
  
  <!-- display thumbnail as icon -->
  <xsl:template name="thumbnail">
      <img src="{$thumbUrl}{$repoUrl}{../@path}/{@href}" vspace="5" hspace="5" border="0"/>
  </xsl:template>

  <!-- get file extension from attribute @href or param 'filename' -->
  <!-- currently handles three letter extension only -->
  <xsl:template name="getFiletype">
    <xsl:param name="filename" select="@href"/>
    <xsl:variable name="type" select="substring-after($filename,'.')"/>
    <xsl:choose>
      <xsl:when test="string-length($type)>4">
      	<xsl:call-template name="getFiletype">
      		<xsl:with-param name="getFilename" select="$type"/>
      	</xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
      	<xsl:variable name="lcletters">abcdefghijklmnopqrstuvwxyz</xsl:variable>
    	<xsl:variable name="ucletters">ABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>
      	<xsl:value-of select="translate($type,$ucletters,$lcletters)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
