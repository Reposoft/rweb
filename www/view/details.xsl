<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:output method="html" encoding="UTF-8" omit-xml-declaration="no" indent="no"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="*"/>
	</xsl:template>
	
	<xsl:template match="lists">
		<xsl:apply-templates select="*"/>
	</xsl:template>
	
	<xsl:template match="list">
		<xsl:apply-templates select="*"/>
	</xsl:template>
	
	<xsl:template match="entry[@kind='dir']">
		<xsl:param name="id" select="generate-id()"/>
		<div class="details" id="d:{$id}">
			<div class="revision">
				<xsl:value-of select="commit/@revision"/>
			</div>
			<div class="username">
				<xsl:value-of select="commit/author"/>
			</div>
			<div class="datetime">
				<xsl:value-of select="commit/date"/>
			</div>
		</div>
	</xsl:template>	
	
	<xsl:template match="entry[@kind='file']">
		<xsl:param name="id" select="generate-id()"/>
		<div class="details" id="d:{$id}">
			<div class="filesize">
				<xsl:value-of select="size"/>
			</div>
			<div class="revision">
				<xsl:value-of select="commit/@revision"/>
			</div>
			<div class="username">
				<xsl:value-of select="commit/author"/>
			</div>
			<div class="datetime">
				<xsl:value-of select="commit/date"/>
			</div>
			<xsl:if test="lock">
				<div class="lock">
					<span class="username">
						<xsl:value-of select="lock/owner"/>
					</span>
					<span class="datetime">
						<xsl:value-of select="lock/created"/>
					</span>
				</div>
			</xsl:if>
		</div>
	</xsl:template>
	
</xsl:stylesheet>
