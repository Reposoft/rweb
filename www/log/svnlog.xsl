<?xml version="1.0"?>

<!-- Sample html conversion stylesheet for the subversion commit stats 1.01 -->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" 
 doctype-system="http://www.w3.org/TR/html4/loose.dtd" indent="yes"/>

<!-- This parameter should be passed by the xslt processor -->

<xsl:param name="title" select="'Log'"/>

<!-- Change this constants if you wish -->

<xsl:variable name="header.commit-statistics" select="'Commit statistics'"/>
<xsl:variable name="header.changelog" select="'Changelog'"/>
<xsl:variable name="header.revisions" select="'Revision history'"/>

<xsl:template match="/">
	<xsl:apply-templates select="log"/>
</xsl:template>

<xsl:template match="log">
  <html>
    <head>
      <title><xsl:value-of select="$title"/></title>
      <link rel="stylesheet" href="svnlog.css" type="text/css"/>
    </head>
    <body>
    <div id="content">
      <h1><xsl:value-of select="$title"/></h1>
	  <h2><xsl:value-of select="$header.changelog"/></h2>     
	  <div id="revisions">
    	<h3><xsl:value-of select="$header.revisions"/></h3>
    	<xsl:apply-templates select="logentry"/>
  	  </div> 
    </div>
    </body>
  </html>
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
