<?xml version="1.0"?>

<!-- Sample html conversion stylesheet for the subversion commit stats 1.01 -->
<!-- run 'svn log -v --xml repository-url-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" 
 doctype-system="http://www.w3.org/TR/html4/loose.dtd" indent="yes"/>

<!-- This parameter should be passed by the xslt processor -->

<xsl:param name="title" select="'Log'"/>


<!-- Change this constants if you wish -->

<xsl:variable name="header.commit-statistics" select="'Commit statistics'"/>
<xsl:variable name="header.changelog" select="'Changelog'"/>
<xsl:variable name="header.authors" select="'List of authors'"/>
<xsl:variable name="header.files" select="'List of files'"/>
<xsl:variable name="header.revisions" select="'Revision history'"/>

<xsl:variable name="author.commits.total" select="'Total commits'"/>
<xsl:variable name="author.commits.first" select="'first'"/>
<xsl:variable name="author.commits.last" select="'last'"/>

<xsl:variable name="file.commits.total" select="'Total commits'"/>
<xsl:variable name="file.commits.first" select="'first'"/>
<xsl:variable name="file.commits.last" select="'last'"/>

<xsl:template match="log">
  <html>
    <head>
      <title><xsl:value-of select="$title"/></title>
      <link rel="stylesheet" href="changelog.css" type="text/css"/>
    </head>
    <body>
    <div id="content">
      <h1><xsl:value-of select="$title"/></h1>
      <h2><xsl:value-of select="$header.commit-statistics"/></h2>
      <xsl:apply-templates select="authors|files"/>
	  <h2><xsl:value-of select="$header.changelog"/></h2>      
      <xsl:apply-templates select="logentries"/>
    </div>
    </body>
  </html>
</xsl:template>

<xsl:template match="authors">
  <div id="authors">
    <h3><xsl:value-of select="$header.authors"/></h3>
    <xsl:apply-templates select="author"/>
  </div>
</xsl:template>

<xsl:template match="author">
  <div class="author">
    <h4 class="summary">
      <span class="name"><xsl:value-of select="name"/></span>
    </h4>
    <xsl:apply-templates select="commits"/>
    <xsl:if test="msg">
      <div class="msg"><xsl:value-of select="msg"/></div>
    </xsl:if>
    <xsl:apply-templates select="paths"/>
  </div>
</xsl:template>

<xsl:template match="author/commits">
   <ul class="commits">
     <li><span class="total"><xsl:value-of select="$author.commits.total"/></span><xsl:text> </xsl:text><span class="value"><xsl:value-of select="total"/></span></li>
     <li><span class="first"><xsl:value-of select="$author.commits.first"/></span><xsl:text> </xsl:text><span class="value"><xsl:value-of select="first"/></span></li>
     <li><span class="last"><xsl:value-of select="$author.commits.last"/></span><xsl:text> </xsl:text><span class="value"><xsl:value-of select="last"/></span></li>
   </ul>
</xsl:template>


<xsl:template match="files">
  <div id="files">
    <h3><xsl:value-of select="$header.files"/></h3>
    <xsl:apply-templates select="file"/>
  </div>
</xsl:template>

<xsl:template match="file">
  <div class="file">
    <h4 class="summary">
      <span class="name"><xsl:value-of select="path"/></span>
    </h4>
    <xsl:apply-templates select="commits"/>
    <xsl:if test="msg">
      <div class="msg"><xsl:value-of select="msg"/></div>
    </xsl:if>
    <xsl:apply-templates select="paths"/>
  </div>
</xsl:template>

<xsl:template match="file/commits">
   <ul class="commits">
     <li><span class="total"><xsl:value-of select="$file.commits.total"/></span><xsl:text> </xsl:text><span class="value"><xsl:value-of select="total"/></span></li>
     <li><span class="first"><xsl:value-of select="$file.commits.first"/></span><xsl:text> </xsl:text><span class="value"><xsl:value-of select="first"/></span></li>
     <li><span class="last"><xsl:value-of select="$file.commits.last"/></span><xsl:text> </xsl:text><span class="value"><xsl:value-of select="last"/></span></li>
   </ul>
</xsl:template>


<xsl:template match="logentries">
  <div id="revisions">
    <h3><xsl:value-of select="$header.revisions"/></h3>
    <xsl:apply-templates/>
  </div>
</xsl:template>

<xsl:template match="logentry">
  <div class="revision">
    <h4 class="summary">
      <span class="revnr"><xsl:value-of select="@revision"/></span><xsl:text> </xsl:text>
      <span class="author"><xsl:value-of select="author"/></span><xsl:text> </xsl:text>
      <span class="date"><xsl:value-of select="date"/></span>
    </h4>
    <xsl:if test="msg">
      <div class="msg"><xsl:value-of select="msg"/></div>
    </xsl:if>
    <xsl:apply-templates select="paths"/>
  </div>
</xsl:template>

<xsl:template match="paths">
  <ul class="paths"><xsl:apply-templates select="path"/></ul>
</xsl:template>

<xsl:template match="paths/path">
  <li>
    <span class="action"><xsl:value-of select="@action"/></span>
    <xsl:text> </xsl:text>
    <span class="value"><xsl:value-of select="."/></span>
  </li>
</xsl:template>

</xsl:stylesheet>
