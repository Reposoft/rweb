<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml">
<xsl:output standalone="no"/>

<xsl:param name="rootUrl" select="'http://www.repos.se'"/><!-- get from repos.properties -->
<!-- layout properties -->
<xsl:param name="imageUrl" select="'{$rootUrl}/images/'"/>
<xsl:param name="cssUrl" select="'{$rootUrl}/css/'"/>
<xsl:param name="jsUrl" select="'{$rootUrl}/js/'"/>
    
<!-- *** replace newline with <br> *** -->
<xsl:template name="linebreak">
    <xsl:param name="text"/>
    <xsl:choose>
        <xsl:when test="contains($text, '&#13;')">
            <xsl:value-of select="substring-before($text, '&#13;')"/>
            <br/>
            <xsl:call-template name="linebreak">
                <xsl:with-param name="text" select="substring-after($text, '&#13;')"/>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$text"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ** Textarea **
	Textarea tags can not be empty in XHTML
	The defualt contents here is a space
 -->
<xsl:template name="textarea">
    <xsl:param name="text"></xsl:param>
	<xsl:param name="name"><xsl:value-of select="'text'"/></xsl:param>
	<xsl:param name="cols"><xsl:value-of select="'50'"/></xsl:param>
	<xsl:param name="rows"><xsl:value-of select="'3'"/></xsl:param>
	<xsl:param name="class"><xsl:value-of select="'form'"/></xsl:param>
	<xsl:param name="style"><xsl:value-of select="'height:200px'"/></xsl:param>
	<textarea rows="{$rows}" cols="{$cols}" name="{$name}" class="{$class}" style="{$style}" onfocus="if ((this.value=='&#160;') || (this.value==' ')) this.value='';">
		<xsl:value-of select="$text"/>
		<xsl:if test="string-length($text)=0">&#160;</xsl:if>
	</textarea>
</xsl:template>


<!-- ** window head **
	prints top bar of windows incl title
 -->
<xsl:template name="windowhead">
    <xsl:param name="title">&#160;</xsl:param>
	<div class="windowhead" style="width:100%; height:17px">
		<img src="{$rootUrl}images/corner.png" width="5" height="17" border="0" hspace="0" vspace="0" align="right"/>
		<span class="window head"><xsl:value-of select="$title"/></span>
	</div>
</xsl:template>


<!-- shared entries for <head> tag. All pages should call this template. -->
<xsl:template name="commonHeaders">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link href="{$cssUrl}main.css" rel="stylesheet" type="text/css" />
	<!-- Script tags do some monkey business with XHTML. Page gets corrupted if they don't have contents. -->
	<script language="JavaScript" type="text/javascript" src="{$jsUrl}navigation.js">&#160;</script>
</xsl:template>

<!-- shared commandbar buttons, expects to be inside a <tr> -->
<xsl:template name="commandbarShared">
	<xsl:variable name="rootName" select="name(/*[1])"/>
	<td id="contextButtons" class="commandbarGlobals" align="right">
		<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<xsl:if test="count(/*[1]/*)=1 and substring($rootName,string-length($rootName)-2)='One' and /*[1]/*[1]/@id">
				<td class="commandbarGlobal">
					<a href="../news/showAdd.jwa?attachment={/*[1]/*[1]/@id}" onfocus="this.blur()"><img title="Message" src="{$rootUrl}images/button_reply.gif" border="0" width="15" height="15"/></a>
				</td>
			</xsl:if>
			<td class="commandbarGlobal">
				<a href="javascript:location.reload()" onfocus="this.blur()"><img title="Reload" src="{$rootUrl}images/button_reload.gif" border="0" width="15" height="15"/></a>
			</td>
			<td class="commandbarGlobal">
				<a href="javascript:window.print()" onfocus="this.blur()"><img alt="Print" src="{$rootUrl}images/button_print.gif" border="0" width="15" height="15"/></a>
			</td>
			<td class="commandbarGlobal">
				<a href="javascript:popup('feedback.jwa','width=600,height=400')" onfocus="this.blur()"><img border="0" alt="ReportProblem" src="{$rootUrl}images/button_issue.gif" width="15" height="15"/></a>
			</td>
			<td class="commandbarGlobal">
				<a target="_blank" href="help.jsp?view={$rootName}" onfocus="this.blur()"><img border="0" alt="Help" src="{$rootUrl}images/button_help.gif" width="15" height="15"/></a>
			</td>
			<td class="commandbarGlobal">
				<a target="_top" href="{$rootUrl}logout.jwa" onfocus="this.blur()"><img alt="LogOut" src="{$rootUrl}images/button_logout.gif" border="0" width="15" height="15"/></a>
			</td>
		</tr>
		</table>
	</td>    
</xsl:template>

<!-- Command bar icon as XML, nodename 'command', attributes 'name', 'action' and optional 'id' -->
<xsl:template match="command">
	<xsl:call-template name="commandbarItem">
		<xsl:with-param name="text"><xsl:value-of select="@name"/></xsl:with-param>
		<xsl:with-param name="href">
            <xsl:value-of select="@action"/><xsl:value-of select="'.jwa'"/>
            <xsl:if test="../@id"><xsl:value-of select="'?id='"/><xsl:value-of select="../@id"/></xsl:if>
		</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<!-- generate icon for commandbar, expects to be inside a <tr> -->
<xsl:template name="commandbarItem">
	<xsl:param name="text"></xsl:param>
	<xsl:param name="href"></xsl:param>
	<td>
		<xsl:call-template name="actionButton">
			<xsl:with-param name="text"><xsl:value-of select="$text"/></xsl:with-param>
			<xsl:with-param name="href"><xsl:value-of select="$href"/></xsl:with-param>
		</xsl:call-template>
	</td>
</xsl:template>


<!-- ******* ACTION BUTTON ******* -->
<!-- target="_blank" is accomplished by using javascript:window.open('url'); -->
<xsl:template name="actionButton">
	<xsl:param name="text"></xsl:param>
	<xsl:param name="href"></xsl:param>
	<xsl:param name="link">
		<xsl:if test="starts-with($href, 'javascript:')"><xsl:value-of select="substring-after($href, 'javascript:')"/></xsl:if>
		<xsl:if test="not(starts-with($href, 'javascript:'))">location.href='<xsl:value-of select="$href"/>'</xsl:if>
	</xsl:param>
	<div class="actionButton" onclick="{$link}">
		<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td class="actionButton left"><img src="{$rootUrl}images/spacer.gif" width="10" height="15" border="0"/></td>
			<td class="actionButton middle">
				<span class="actionButton"><xsl:value-of select="$text"/></span>
			</td>
			<td class="actionButton right"><img src="{$rootUrl}images/spacer.gif" width="10" height="15" border="0"/></td>
		</tr>
		</table>
	</div>
</xsl:template>




<!--
***********************
*** STATUS SELECTOR ***
***********************
     <xsl:with-param name="name">status</xsl:with-param>
     <xsl:with-param name="selected">{0-6}</xsl:with-param>
-->

<xsl:template name="statusSelector">
  <xsl:param name="name" select="'status'"/>
  <xsl:param name="selected" select="'0'"/>

  <select name="{$name}">
    <option value="0">
      <xsl:if test="$selected=0"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
      Unaccepted
    </option>
    <option value="1">
      <xsl:if test="$selected=1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
      0%
    </option>
    <option value="2">
      <xsl:if test="$selected=2"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
      25%
    </option>
    <option value="3">
      <xsl:if test="$selected=3"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
      50%
    </option>
    <option value="4">
      <xsl:if test="$selected=4"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
      75%
    </option>
    <option value="5">
      <xsl:if test="$selected=5"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
      Finished
    </option>
    <option value="6">
      <xsl:if test="$selected=6"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
      NoAction
    </option>
  </select>
</xsl:template>

<xsl:template name="statusName">
  <xsl:param name="selected" select="'0'"/>
  <xsl:choose>
    <xsl:when test="$selected=0">Unaccepted</xsl:when>
    <xsl:when test="$selected=1">0%</xsl:when>
    <xsl:when test="$selected=2">25%</xsl:when>
    <xsl:when test="$selected=3">50%</xsl:when>
    <xsl:when test="$selected=4">75%</xsl:when>
    <xsl:when test="$selected=5">Finished</xsl:when>
    <xsl:when test="$selected=6">NoAction</xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template name="statusImage">
	<xsl:param name="status" select="'0'"/>
	<xsl:param name="overdue" select="'0'"/>
	<img width="90" height="9">
		<xsl:choose>
			<xsl:when test="$status=5 or $status=6">
				<xsl:attribute name="class"><xsl:value-of select="'status inactive'"/></xsl:attribute>
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="$overdue=1">
						<xsl:attribute name="class"><xsl:value-of select="'status overdue'"/></xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="class"><xsl:value-of select="'status'"/></xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:choose>
			<xsl:when test="$status=0">
					<xsl:attribute name="src"><xsl:value-of select="$rootUrl"/><xsl:value-of select="'images/status/status_unacc.gif'"/></xsl:attribute>
					<xsl:attribute name="alt">Unaccepted</xsl:attribute>
			</xsl:when>
			<xsl:when test="$status=1">
					<xsl:attribute name="src"><xsl:value-of select="$rootUrl"/><xsl:value-of select="'images/status/status_0.gif'"/></xsl:attribute>
					<xsl:attribute name="alt">0%</xsl:attribute>
			</xsl:when>
			<xsl:when test="$status=2">
					<xsl:attribute name="src"><xsl:value-of select="$rootUrl"/><xsl:value-of select="'images/status/status_25.gif'"/></xsl:attribute>
					<xsl:attribute name="alt">25%</xsl:attribute>
			</xsl:when>
			<xsl:when test="$status=3">
					<xsl:attribute name="src"><xsl:value-of select="$rootUrl"/><xsl:value-of select="'images/status/status_50.gif'"/></xsl:attribute>
					<xsl:attribute name="alt">50%</xsl:attribute>
			</xsl:when>
			<xsl:when test="$status=4">
					<xsl:attribute name="src"><xsl:value-of select="$rootUrl"/><xsl:value-of select="'images/status/status_75.gif'"/></xsl:attribute>
					<xsl:attribute name="alt">75%</xsl:attribute>
			</xsl:when>
			<xsl:when test="$status=5">
					<xsl:attribute name="src"><xsl:value-of select="$rootUrl"/><xsl:value-of select="'images/status/status_100.gif'"/></xsl:attribute>
					<xsl:attribute name="alt">Completed</xsl:attribute>
			</xsl:when>
			<xsl:when test="$status=6">
					<xsl:attribute name="src"><xsl:value-of select="$rootUrl"/><xsl:value-of select="'images/status/status_0.gif'"/></xsl:attribute>
					<xsl:attribute name="alt">NoAction</xsl:attribute>
			</xsl:when>
		</xsl:choose>
	</img>
</xsl:template>



<!--
***********************
*** RECEIVER SELECT ***
***********************
     <xsl:with-param name="selected">Selected</xsl:with-param>
     <xsl:with-param name="notSelected">NotSelected</xsl:with-param>
-->

<xsl:template match="receiverSelect">
	<xsl:param name="notSelected" select="'NotSelected'"/>
	<xsl:param name="selected" select="'Selected'"/>

	<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><span class="form heading"><xsl:value-of select="$notSelected"/></span></td>
		<td>&#160;</td>
		<td><span class="form heading"><xsl:value-of select="$selected"/></span></td>
	</tr>
	<tr>
		<td>
			<select class="form multiple" name="{@name}_not" multiple="1">
				<xsl:apply-templates select="*[not(@selected)]"/>
			</select>
		</td>
		<td valign="middle">
			<input class="button tofrom" type="button" onClick="moveSelected(this.form.elements['{@name}_not'], this.form.elements['{@name}'])" value="Add&#160;&#187;"/><br/>
			<input class="button tofrom" type="button" onClick="moveSelected(this.form.elements['{@name}'], this.form.elements['{@name}_not'])" value="&#171;&#160;Remove"/>
		</td>
		<td>
			<select class="form multiple" name="{@name}" multiple="1">
				<xsl:apply-templates select="*[@selected]"/>
			</select>
		</td>
	</tr>
	</table>
</xsl:template>

<xsl:template match="receiverSelect/*">
  <option value="{@id}"><xsl:value-of select="@name"/></option>
</xsl:template>

<!--
******************
*** ADD SELECT ***
******************
     <xsl:with-param name="selected">Selected</xsl:with-param>
     <xsl:with-param name="new">New</xsl:with-param>
-->

<xsl:template match="addSelect">
  <xsl:param name="new" select="'New'"/>
  <xsl:param name="selected" select="'Selected'"/>

	<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><span class="form heading"><xsl:value-of select="$new"/></span></td>
		<td>&#160;</td>
		<td><span class="form heading"><xsl:value-of select="$selected"/></span></td>
	</tr>
	<tr>
		<td valign="top">
			<input class="form" name="{@name}_edit" style="width:200px"/>
		</td>
		<td valign="top">
			<input class="button tofrom" type="button" onClick="appendSelected(this.form.elements['{@name}'], this.form.elements['{@name}_edit'])" value="Add&#160;&#187;"/><br/>
			<input class="button tofrom" type="button" onClick="editSelected(this.form.elements['{@name}'], this.form.elements['{@name}_edit'])" value="&#171;&#160;Edit"/><br/>
			<input class="button tofrom" type="button" onClick="removeSelected(this.form.elements['{@name}'])" value="Remove"/>
		</td>
		<td>
			<select class="form multiple" name="{@name}" multiple="1">
				 <xsl:apply-templates select="addSelectItem"/>
			</select>
		</td>
	</tr>
	</table>
</xsl:template>

<xsl:template match="addSelect/addSelectItem">
  <option value="{@value}"><xsl:value-of select="@value"/></option>
</xsl:template>

<!-- document numbering -->
<!-- used in document and unit -->
<xsl:template name="documentNumberingDigits">
	<xsl:param name="name" select="'numberOfDigits'"/>
	<xsl:param name="disabled" select="'0'"/>
	<xsl:param name="selected" select="'1'"/>
	<select class="form" name="{$name}">
		<xsl:if test="$disabled=1">
			<xsl:attribute name="class"><xsl:value-of select="'form disabled'"/></xsl:attribute>
			<xsl:attribute name="disabled"><xsl:value-of select="'1'"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="not($selected=0)"><option value="0">NoNumber</option></xsl:if>
		<xsl:if test="$selected=0"><option value="0" selected="1">NoNumber</option></xsl:if>

		<xsl:if test="not($selected=1)"><option value="1">Floating</option></xsl:if>
		<xsl:if test="$selected=1"><option value="1" selected="1">Floating</option></xsl:if>

		<xsl:if test="not($selected=2)"><option value="2">2</option></xsl:if>
		<xsl:if test="$selected=2"><option value="2" selected="1">2</option></xsl:if>

		<xsl:if test="not($selected=3)"><option value="3">3</option></xsl:if>
		<xsl:if test="$selected=3"><option value="3" selected="1">3</option></xsl:if>

		<xsl:if test="not($selected=4)"><option value="4">4</option></xsl:if>
		<xsl:if test="$selected=4"><option value="4" selected="1">4</option></xsl:if>

		<xsl:if test="not($selected=5)"><option value="5">5</option></xsl:if>
		<xsl:if test="$selected=5"><option value="5" selected="1">5</option></xsl:if>

		<xsl:if test="not($selected=6)"><option value="6">6</option></xsl:if>
		<xsl:if test="$selected=6"><option value="6" selected="1">6</option></xsl:if>

		<xsl:if test="not($selected=7)"><option value="7">7</option></xsl:if>
		<xsl:if test="$selected=7"><option value="7" selected="1">7</option></xsl:if>

		<xsl:if test="not($selected=8)"><option value="8">8</option></xsl:if>
		<xsl:if test="$selected=8"><option value="8" selected="1">8</option></xsl:if>
	</select>
</xsl:template>


</xsl:stylesheet>