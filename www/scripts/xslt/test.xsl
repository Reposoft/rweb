<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template match="/">
  	<div id="singleroot">
   	<h2>Colorado Rock Climbing</h2>
    <table border="0">
    <tr>
      <th align="left">Route</th>
      <th align="left">Grade</th>

	  <th align="left">Type</th>
	  <th align="left">Location</th>
	  <th align="left">First Ascent</th>
    </tr>
    <xsl:for-each select="routes/route">
    <tr>
      <td><xsl:value-of select="name"/></td>

      <td><xsl:value-of select="grade"/></td>
	  <td><xsl:value-of select="@type"/></td>
      <td><xsl:value-of select="location"/></td>
	  <td><xsl:value-of select="firstAscent"/></td>
    </tr>
    </xsl:for-each>
    </table>
	</div>
  </xsl:template>
</xsl:stylesheet>
