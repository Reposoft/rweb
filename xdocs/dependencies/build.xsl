<?xml version="1.0"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    version="1.0">
  <xsl:output method="xml" indent="yes"/>

<xsl:template match="index">
    <document>
    <properties>
        <author email="repos.se@gmail.com">repos.se</author>
        <title>repos.se dependency: <xsl:value-of select="@name"/></title>
    </properties>
    <body>
    <section name="{@name}">
        <p>Open source packages are plugged in to Repos using an Ant build script. See the prototype dependency /www/tinymce for a sample script.</p>
        <table>
       <xsl:apply-templates select="project" mode="tr"/>
        </table>
    </section>
    </body>
    </document>
</xsl:template>
  
<xsl:template match="project">
    <document>
    <properties>
        <author email="repos.se@gmail.com">repos.se</author>
        <title>repos.se dependency: <xsl:value-of select="@name"/></title>
    </properties>
    <body>
    <section name="{@name}">
       <xsl:apply-templates select="*"/>
    </section>
    </body>
    </document>
</xsl:template>

<xsl:template match="project" mode="tr">
    <xsl:param name="package" select="property[@name='repos-package']/@value"/>
    <tr><th>
          <a href="{$package}.build.html"><xsl:value-of select="$package"/></a> 
    </th>
    <td><xsl:value-of select="@name"/></td>
    <td><xsl:value-of select="description"/></td></tr>
</xsl:template>

<!-- match all targets with empty contents to avoid printing text contents -->
<xsl:template match="target">
</xsl:template>

<!-- default target layout -->
<xsl:template match="target">
</xsl:template>

<xsl:template match="target[@name='download']">
    <section name="{@name}">
    <xsl:apply-templates select="." mode="default"/>
    <table>
        <xsl:apply-templates select="*" mode="tr"/>
    </table>
    </section>
</xsl:template>

<xsl:template match="target[@name='license']">
    <section name="{@name}">
    <table>
        <xsl:apply-templates select="*" mode="tr"/>
    </table>
    </section>
</xsl:template>

<xsl:template match="target[@name='install']">
    <section name="{@name}">
    <table>
        <xsl:apply-templates select="*" mode="tr"/>
    </table>
    </section>
</xsl:template>

<xsl:template match="target[@name='clean']">
    <section name="{@name}">
    <table>
        <xsl:apply-templates select="*" mode="tr"/>
    </table>
    </section>
</xsl:template>

<xsl:template match="description">
    <p><xsl:value-of select="."/></p>
</xsl:template>

<xsl:template match="echo">
    <p><xsl:value-of select="."/></p>
</xsl:template>

<xsl:template match="echo" mode="tr">
    <tr><td colspan="2"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="property">
    <p><b><xsl:value-of select="translate(@name,'_',' ')"/>:</b>&#160;<xsl:value-of select="@value"/></p>
</xsl:template>

<xsl:template match="property" mode="tr">
    <tr><th><xsl:value-of select="translate(@name,'_',' ')"/></th><td><xsl:value-of select="@value"/></td></tr>
</xsl:template>

</xsl:stylesheet>