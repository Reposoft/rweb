<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html"/>

  <!-- settings -->
  <xsl:param name="thumbUrl">http://www.optime.se/system/phpThumb/phpThumbq.php?src=</xsl:param>
  <xsl:param name="repoPath">http://localhost/repos/ordbehandlare</xsl:param>
  <xsl:param name="stylesheet">http://www.optime.se/repos/static/ordbehandlare.css</xsl:param>
  <xsl:param name="editPath">http://www.optime.se/repos</xsl:param>

  <!-- repos.se shared templates include -->

  <!-- local templates -->
  <xsl:template match="*"/>

  <xsl:template match="svn">
    <html>
      <head>
        <title>
          <xsl:if test="string-length(index/@name) != 0">
            <xsl:value-of select="index/@name"/>
            <xsl:text>: </xsl:text>
          </xsl:if>
          <xsl:value-of select="index/@path"/>
        </title>
        <link rel="stylesheet" type="text/css" href="{$stylesheet}"/>
      </head>
      <body>
        <div class="svn">
          <xsl:apply-templates/>
        </div>
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
      </body>
    </html>
  </xsl:template>

  <xsl:template match="index">
    <div class="rev">
      <xsl:if test="string-length(@name) != 0">
        <xsl:value-of select="@name"/>
        <xsl:if test="string-length(@rev) != 0">
          <xsl:text> &#8212; </xsl:text>
        </xsl:if>
      </xsl:if>
      <xsl:if test="string-length(@rev) != 0">
        <xsl:text>Revision </xsl:text>
        <xsl:value-of select="@rev"/>
      </xsl:if>
    </div>
    <div class="path">
      <xsl:value-of select="@path"/>
    </div>
    <xsl:apply-templates select="updir"/>
    <xsl:apply-templates select="dir"/>
    <xsl:apply-templates select="file"/>
  </xsl:template>

  <xsl:template match="updir">
    <div class="updir">
      <xsl:text>[</xsl:text>
      <xsl:element name="a">
        <xsl:attribute name="href">..</xsl:attribute>
        <xsl:text>Parent Directory</xsl:text>
      </xsl:element>
      <xsl:text>]</xsl:text>
    </div>
    <!-- xsl:apply-templates/ -->
  </xsl:template>

  <xsl:template match="dir">
    <div class="dir">
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="@href"/>
        </xsl:attribute>
        <xsl:value-of select="@name"/>
        <xsl:text>/</xsl:text>
      </xsl:element>
    </div>
    <!-- <xsl:apply-templates/ -->
  </xsl:template>

  <xsl:template match="file">
    <div class="file">
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="@href"/>
        </xsl:attribute>
        <xsl:call-template name="icon"/>
        <xsl:value-of select="@name"/>
      </xsl:element>
      <!-- adding edit link, currently for all type of contents -->
      <xsl:text>  </xsl:text>
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="$editPath"/>
          <xsl:value-of select="'/'"/>
          <!-- ...file.type.jwa -->
          <xsl:value-of select="@name"/>
          <xsl:value-of select="'.jwa'"/>
          <!-- repo not ending with '/' -->
          <xsl:value-of select="'?repo='"/><xsl:value-of select="$repoPath"/>
          <!-- path not ending with '/' -->
          <xsl:value-of select="'&amp;path='"/><xsl:value-of select="../@path"/>
          <!-- filename -->
          <xsl:value-of select="'&amp;file='"/><xsl:value-of select="@href"/>
        </xsl:attribute>
        <xsl:text>[edit]</xsl:text>
      </xsl:element>
    </div>
    <!-- xsl:apply-templates/ -->
  </xsl:template>
  
  <xsl:template name="icon">
  	<!-- input: lowercase file extension -->
  	<xsl:param name="filetype"><xsl:call-template name="filetype"/></xsl:param>
  	<!-- all handled filetypes -->
  	<xsl:choose>
  		<xsl:when test="$filetype = 'jpg'">
  			<img src="{$thumbUrl}{$repoPath}{../@path}/{@href}" vspace="5" hspace="5" border="0"/>
  		</xsl:when>
  		<xsl:otherwise>
  		</xsl:otherwise>
  	</xsl:choose>
  </xsl:template>
  
  <xsl:template name="filetype">
  	<xsl:param name="filename" select="@name"/>
  	<xsl:variable name="type" select="substring-after($filename,'.')"/>
  	<xsl:choose>
  		<xsl:when  test="string-length($type)>4">
	  		<xsl:call-template name="filetype">
	  			<xsl:with-param name="filename" select="$type"/>
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
