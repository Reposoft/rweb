<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml">
<xsl:output method="xml" indent="no"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
    doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" />
<!-- common templates -->
<xsl:include href="../xslt/common.xsl"/>

<xsl:template match="/">
    <html>
        <head>
            <title>coordinator.Activity</title>
            <xsl:call-template name="commonHeaders"/>
            <script language="JavaScript" type="text/javascript" src="{$jsUrl}form.js">&#160;</script>
            <script language="JavaScript" type="text/javascript" src="{$jsUrl}layoutWorkspace.js">&#160;</script>
        </head>
        <body class="workspace" onload="layoutWorkspace(1)" onresize="layoutWorkspace(1)">

		<div id="workspace" style="position:absolute; left:0%; top:0%; width:100%; height:100%; visibility:hidden;">
			<!-- style attrib required here for firefox to get the layers' z-indexes -->
			<div class="commmandbar" style="position:absolute; left:0%; top:0%; width:100%; height:30px; z-index:100;">
				<!-- commandbar -->
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr>
						<xsl:if test="name(/*[1])='activityList' or name(/*[1])='activityGantt'">
							<xsl:call-template name="commandbarItem">
								<xsl:with-param name="text">addActivity</xsl:with-param>
								<xsl:with-param name="href"><xsl:value-of select="'showAdd.jwa'"/></xsl:with-param>
							</xsl:call-template>
						</xsl:if>
						<xsl:if test="name(/*[1])='activityGantt'">
							<xsl:call-template name="commandbarItem">
								<xsl:with-param name="text">showList</xsl:with-param>
								<xsl:with-param name="href"><xsl:value-of select="'.jwa'"/></xsl:with-param>
							</xsl:call-template>
						</xsl:if>
						<xsl:if test="name(/*[1])='activityList'">
							<xsl:call-template name="commandbarItem">
								<xsl:with-param name="text">ShowGantt</xsl:with-param>
								<xsl:with-param name="href"><xsl:value-of select="'gantt.jwa'"/></xsl:with-param>
							</xsl:call-template>
							<xsl:call-template name="commandbarItem">
								<xsl:with-param name="text">ExpandAll</xsl:with-param>
								<xsl:with-param name="href">javascript:setSearch('?tool=9&amp;type=<xsl:value-of select="activityList/@type"/>&amp;<xsl:value-of select="concat('id=',activityList/@id)"/>&amp;expand=<xsl:value-of select="activityList/@expandAll"/>')</xsl:with-param>
							</xsl:call-template>
							<xsl:call-template name="commandbarItem">
								<xsl:with-param name="text">FoldAll</xsl:with-param>
								<xsl:with-param name="href">javascript:setSearch('?tool=9&amp;type=<xsl:value-of select="activityList/@type"/>&amp;<xsl:value-of select="concat('id=',activityList/@id)"/>&amp;expand=')</xsl:with-param>
							</xsl:call-template>
						</xsl:if>
						<xsl:if test="name(/*[1])='activityOne'">
							<xsl:if test="activityOne/activityItem/@editPriv or activityOne/activityItem/@updatePriv">
								<xsl:call-template name="commandbarItem">
									<xsl:with-param name="text">Edit</xsl:with-param>
									<xsl:with-param name="href"><xsl:value-of select="'showEdit.jwa?id='"/><xsl:value-of select="activityOne/activityItem/@id"/></xsl:with-param>
								</xsl:call-template>
							</xsl:if>
							<xsl:if test="activityOne/activityItem/@deletePriv">
								<xsl:variable name="askDeleteActivity">askDeleteActivity?</xsl:variable>
								<xsl:call-template name="commandbarItem">
									<xsl:with-param name="text">Delete</xsl:with-param>
									<xsl:with-param name="href">javascript:confirmRedirect('<xsl:value-of select="$askDeleteActivity"/>','delete.jwa?id=<xsl:value-of select="activityOne/activityItem/@id"/>')</xsl:with-param>
								</xsl:call-template>
							</xsl:if>
						</xsl:if>
						<xsl:call-template name="commandbarShared"/>
					</tr>
				</table>
			</div>
			<div id="column1" style="position:absolute; left:0%; top:0%; width:100%; height:100%; z-index:1"><!-- leftColumn -->
				<div style="position:absolute;" layoutPerc="100">
					<!-- contents -->
					<xsl:apply-templates select="*"/>
				</div>
			</div>
		</div>	
        </body>
    </html>
</xsl:template>

<!-- gantt -->

<xsl:template match="project">
		<xsl:call-template name="windowhead">
			<xsl:with-param name="title">Activities</xsl:with-param>
		</xsl:call-template>
		<div class="windowcontent">
			<!-- today line -->
			<!--<div class="gantt today" style="left:{@now div 100 * 0.8}%; height:{count(activityItem) * 45 + 40}px; clip:rect(0px 2px {count(activityItem) * 40 + 20}px 0px);">&#160;</div>-->
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td id="tdGanttSpace" style="width:80%">
					<div style="position:relative; left:0%; top:0%; width:100%;">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td class="gantt projectspan">
								<table width="100%" cellpadding="0" cellspacing="0" border="0">
								<tr>
									<td><span class="white"><xsl:value-of select="@start"/></span></td>
									<td align="right"><span class="white"><xsl:value-of select="@end"/></span></td>
								</tr>
								</table>
							</td>
						</tr>
						</table>
					</div>
				</td>
				<td width="20%"><!--<a href="javascript:alert(document.getElementById('tdGanttSpace').style.width);">test</a>-->&#160;</td>
			</tr>
				<xsl:apply-templates select="tasks"></xsl:apply-templates>
			<tr>
				<td colspan="2">&#160;</td>
			</tr>
			</table>
		</div>
</xsl:template>

<xsl:template match="project/tasks">
                <xsl:apply-templates select="task">
                	<xsl:sort select="@start" order="ascending"/>
                	<xsl:sort select="@duration" order="ascending"/>
                	<xsl:sort select="@name" order="ascending"/>
                </xsl:apply-templates>
</xsl:template>    
    
<xsl:template match="project/tasks/task">
			<!-- dashline -->
			<tr>
				<td width="80%">
					<div style="position:relative; left:0%; top:0%; width:100%;">
						<div style="position:relative; left:{@left div 100 + @width div 100 + 1}%; top:0%; width:{100 - @left div 100 - @width div 100 - 2}%; clip:rect(0% {100 - @left div 100 - @width div 100 - 2}% 20px 0%); border-bottom:1px dashed #dddddd">&#160;</div>
					</div>
				</td>
				<td width="20%">&#160;</td>
			</tr>
			<!-- status images and name -->
			<tr>
				<td width="80%">
					<div style="position:relative; left:0%; top:0%; width:100%;">
						<div style="position:relative; left:{@left div 100}%; top:0%; width:{@width div 100}%; height:13px; clip:rect(0% {@width div 100}% 13px 0%); padding:1px; cursor:pointer;" onclick="location.href='.jwa?id={@id}'" onfocus="this.blur()">
							<xsl:choose>
								<xsl:when test="@status=5 or @status=6">
									<xsl:attribute name="class"><xsl:value-of select="'status inactive'"/></xsl:attribute>
								</xsl:when>
								<xsl:otherwise>
									<xsl:choose>
										<xsl:when test="@overdue">
											<xsl:attribute name="class"><xsl:value-of select="'status overdue'"/></xsl:attribute>
										</xsl:when>
										<xsl:otherwise>
											<xsl:attribute name="class"><xsl:value-of select="'status'"/></xsl:attribute>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:otherwise>
							</xsl:choose>
							<div class="statusbar">
								<xsl:choose>
									<xsl:when test="@status=0">
										<xsl:attribute name="style"><xsl:value-of select="'width:100%; height:100%; clip:rect(0% 100% 100% 0%); background-color:#ffffff;'"/></xsl:attribute>
										<img style="margin-top:1px; margin-bottom:1px;" alt="" src="{$imageUrl}/status/status_unacc_arrow.gif" width="15" height="9" border="0"/>
									</xsl:when>
									<xsl:when test="@status=1">
										<xsl:attribute name="style"><xsl:value-of select="'width:100%; height:100%; clip:rect(0% 100% 100% 0%); background-color:#ffffff;'"/></xsl:attribute>
									</xsl:when>
									<xsl:when test="@status=2">
										<xsl:attribute name="style"><xsl:value-of select="'width:25%; height:100%; clip:rect(0% 25% 100% 0%);'"/></xsl:attribute>
									</xsl:when>
									<xsl:when test="@status=3">
										<xsl:attribute name="style"><xsl:value-of select="'width:50%; height:100%; clip:rect(0% 50% 100% 0%);'"/></xsl:attribute>
									</xsl:when>
									<xsl:when test="@status=4">
										<xsl:attribute name="style"><xsl:value-of select="'width:75%; height:100%; clip:rect(0% 75% 100% 0%);'"/></xsl:attribute>
									</xsl:when>
									<xsl:when test="@status=5">
										<xsl:attribute name="style"><xsl:value-of select="'width:100%; height:100%; clip:rect(0% 100% 100% 0%);'"/></xsl:attribute>
									</xsl:when>
								</xsl:choose>
							</div>
						</div>
					</div>
				</td>
				<td width="20%">
					<a href=".jwa?id={@id}" onfocus="this.blur()">
						<xsl:choose>
							<xsl:when test="@overdue and @status='0'">
								<xsl:attribute name="class"><xsl:value-of select="'overdue unread'"/></xsl:attribute>
							</xsl:when>
							<xsl:when test="@overdue and not(@status='0')">
								<xsl:attribute name="class"><xsl:value-of select="'overdue'"/></xsl:attribute>
							</xsl:when>
							<xsl:when test="not(@overdue) and @status='0'">
								<xsl:attribute name="class"><xsl:value-of select="'unread'"/></xsl:attribute>
							</xsl:when>
						</xsl:choose>
						<xsl:value-of select="@name"/>
					   </a>
				</td>
			</tr>
			<!-- date -->
			<tr>
				<td width="80%">
					<div style="position:relative; left:0%; top:0%; width:100%;">
						<div style="position:relative; left:{@left div 100}%; top:0%; width:{@width div 100}%;">
							<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td><span class="italic nobr"><xsl:value-of select="@start"/></span></td>
								<td>&#160;</td>
								<td align="right"><span class="italic nobr"><xsl:value-of select="@end"/></span></td>
							</tr>
							</table>
						</div>
					</div>
				</td>
				<td width="20%">&#160;</td>
			</tr>
</xsl:template>
    
<!-- 
****************
*** ACTIVITY ***
****************
-->

<!-- Activity "ShowAll" -->

<xsl:template match="activityList">
		<xsl:call-template name="windowhead">
			<xsl:with-param name="title">Activities</xsl:with-param>
		</xsl:call-template>
		<div class="windowcontent">
			<table cellspacing="0" cellpadding="0" border="0" width="100%" summary="newsList">
			<tr>
				<td class="listhead">&#160;</td>
				<td class="listhead" width="100%"><span class="listhead">Name</span></td>
				<td class="listhead">&#160;</td>
				<td class="listhead"><span class="listhead">Start</span></td>
				<td class="listhead"><span class="listhead">End</span></td>
				<td class="listhead end">&#160;</td>
			</tr>
			<tr>
				<td colspan="6">
					<img alt="" src="{$imageUrl}spacer.gif" width="1" height="2" border="0"/>
				</td>
			</tr>
			<xsl:apply-templates select="activityItem">
				<xsl:sort select="@end" order="ascending"/>
				<xsl:sort select="@start" order="ascending"/>
				<xsl:sort select="@name" order="ascending"/>
			</xsl:apply-templates>
			<tr>
				<td colspan="6">
					<img alt="" src="{$imageUrl}spacer.gif" width="1" height="2" border="0"/>
				</td>
			</tr>
			</table>
		</div>
</xsl:template>

<xsl:template match="activityList/activityItem">
	<tr>
		<td class="list expander">
			<xsl:if test="not(@expanded)">
				<a href="javascript:setSearch('?type={../@type}&amp;id={../@id}&amp;expand={@expand}{@id}_')"><img src="{$imageUrl}arrow_coll.gif" border="0" alt="Expand"/></a>
			</xsl:if>
			<xsl:if test="@expanded">
				<a href="javascript:setSearch('?type={../@type}&amp;id={../@id}&amp;expand={@unexpand}')"><img src="{$imageUrl}arrow_exp.gif" border="0" alt="Fold"/></a>
			</xsl:if>
		</td>
		<td class="list">
			<a href=".jwa?id={@id}" onfocus="this.blur()">
                <xsl:choose>
					<xsl:when test="@overdue and @status='0'">
						<xsl:attribute name="class"><xsl:value-of select="'overdue unread'"/></xsl:attribute>
					</xsl:when>
					<xsl:when test="@overdue and not(@status='0')">
						<xsl:attribute name="class"><xsl:value-of select="'overdue'"/></xsl:attribute>
					</xsl:when>
					<xsl:when test="not(@overdue) and @status='0'">
						<xsl:attribute name="class"><xsl:value-of select="'unread'"/></xsl:attribute>
					</xsl:when>
                </xsl:choose>
                <xsl:value-of select="@name"/>
               </a>
		</td>
		<td class="list">
			<xsl:call-template name="statusImage">
				<xsl:with-param name="status"><xsl:value-of select="@status"/></xsl:with-param>
				<xsl:with-param name="overdue"><xsl:value-of select="@overdue"/></xsl:with-param>
			</xsl:call-template>
		</td>
		<td class="list nobr"><xsl:value-of select="@start"/></td>
		<td class="list nobr"><xsl:value-of select="@end"/></td>
		<td class="list button end">
			<xsl:if test="@editPriv">
				<a href="showEdit.jwa?id={@id}"><img src="{$imageUrl}button_edit.gif" width="15" height="15" border="0" alt="Edit" hspace="1"/></a>
			</xsl:if>
			<xsl:if test="not(@editPriv)">
				<img alt="" src="{$imageUrl}spacer.gif" width="15" height="15" border="0" hspace="1"/>
			</xsl:if>
			<xsl:if test="@deletePriv">
				<xsl:variable name="askDeleteActivity">askDeleteActivity?</xsl:variable>
				<a href="javascript:confirmRedirect({$askDeleteActivity},'delete.jwaid={@id}')"><img src="{$imageUrl}button_delete.gif" width="15" height="15" border="0" alt="Delete" hspace="1"/></a>
			</xsl:if>
			<xsl:if test="not(@deletePriv)">
				<img alt="" src="{$imageUrl}spacer.gif" width="15" height="15" border="0" hspace="1"/>
			</xsl:if>
		</td>
	</tr>
	<xsl:if test="@expanded">
		<tr>
			<td>&#160;</td>
			<td>
				<xsl:call-template name="linebreak">
					<xsl:with-param name="text" select="."/>
				</xsl:call-template>
			</td>
			<td colspan="5">&#160;</td>
		</tr>
	</xsl:if>
	<tr>
		<td colspan="6">
			<img alt="" src="{$imageUrl}spacer.gif" width="1" height="2" border="0"/>
		</td>
	</tr>
	<tr>
		<td class="dashline" colspan="6">
			<img alt="" src="{$imageUrl}spacer.gif" width="1" height="1" border="0"/>
		</td>
	</tr>
	<tr>
		<td colspan="6">
			<img alt="" src="{$imageUrl}spacer.gif" width="1" height="1" border="0"/>
		</td>
	</tr>	
</xsl:template>

<xsl:template match="activityDenied">
  <h1>permission denied</h1>
  <p>Need <xsl:value-of select="@priv"/> on object.</p>
</xsl:template>


<!-- 
*****************
*** ACTIVITY  ***
*****************
-->

<!--ShowOne -->

<xsl:template match="activityOne">
	<xsl:param name="askDeleteActivity">askDeleteActivity?</xsl:param>
    <xsl:apply-templates select="activityItem"/>
	<div class="windowcontent bottom">
		<div class="buttons">
			<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td>
					<xsl:call-template name="actionButton">
						<xsl:with-param name="text">Back</xsl:with-param>
						<xsl:with-param name="href">javascript:closeView()</xsl:with-param>
					</xsl:call-template>	
				</td>
			</tr>
			</table>
		</div>
	</div>
</xsl:template>

<xsl:template match="activityOne/activityItem">
	<xsl:call-template name="windowhead">
		<xsl:with-param name="title"><xsl:value-of select="@name"/></xsl:with-param>
	</xsl:call-template>
	<div class="windowcontent top">
		<div class="item">
			<div>
				<xsl:call-template name="statusImage">
					<xsl:with-param name="status"><xsl:value-of select="@status"/></xsl:with-param>
					<xsl:with-param name="overdue"><xsl:value-of select="@overdue"/></xsl:with-param>
				</xsl:call-template>
			</div>
			<div style="padding-top:4px;">
				<span class="italic nobr"><xsl:value-of select="@start"/><xsl:value-of select="' - '"/><xsl:value-of select="@end"/></span>
			</div>
			<div class="item main">
				<xsl:call-template name="linebreak">
					<xsl:with-param name="text" select="."/>
				</xsl:call-template>
			</div>
			<div>
				<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="table heading" valign="top"><span class="table heading">Responsible</span></td>
					<td>
						<table cellpadding="0" cellspacing="0" border="0">
							<xsl:for-each select="userItem">
								<tr>
									<td><xsl:value-of select="@name"/></td>
								</tr>
							</xsl:for-each>
						</table>
					</td>
				</tr>
				</table>
			</div>
		</div>
	</div>
</xsl:template>

<xsl:template match="activityOne/activityItem/activityResp">
  <xsl:value-of select="@resp_user"/><br/>
</xsl:template>


<!-- add -->

<xsl:template match="activityAdd">
	<xsl:call-template name="windowhead">
		<xsl:with-param name="title">AddActivity</xsl:with-param>
	</xsl:call-template>
	<div class="windowcontent top">
		<form id="addAct" action="add.jwa" method="post">
		<input type="hidden" name="id" value="{@id}" />
		<div class="item">
			<span class="form heading block">Name</span>
			<input class="form block dynamic" type="text" name="name"/>
			<div>&#160;</div>
			<span class="form heading block">Description</span>
			<xsl:call-template name="textarea">
				<xsl:with-param name="text" select="."/>
				<xsl:with-param name="class" select="'form dynamic'"/>
				<xsl:with-param name="name" select="'description'"/>
				<xsl:with-param name="style" select="'height:100px'"/>
			</xsl:call-template>
			<div>&#160;</div>
			<span class="form heading block">Start</span>
			<div>
				<input class="form date" type="text" name="start" value="{@now}"/>&#160;<input class="form time" type="text" name="start_time" value="{@now_time}"/>
			</div>
			<span class="form heading block">End</span>
			<div>
				<input class="form date" type="text" name="end" value="{@now}"/>&#160;<input class="form time" type="text" name="end_time" value="{@now_time}"/>
			</div>
			<div>&#160;</div>
			<span class="form heading block">Status</span>
			<xsl:call-template name="statusSelector">
				<xsl:with-param name="name">status</xsl:with-param>
				<xsl:with-param name="selected">0</xsl:with-param>
			</xsl:call-template>
			<div>&#160;</div>
			<xsl:apply-templates select="receiverSelect">
				<xsl:with-param name="selected">Responsible</xsl:with-param>
				<xsl:with-param name="notSelected">&#160;</xsl:with-param>
			</xsl:apply-templates>
		</div>
		</form>
	</div>
	<div class="windowcontent bottom">
		<div class="buttons">
			<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td>
					<xsl:call-template name="actionButton">
						<xsl:with-param name="text">Save</xsl:with-param>
						<xsl:with-param name="href">javascript:setActionCheckFormSubmit('addAct','add',[['responsible','usersResponsible','receiverSelect','required'],['name','Name','required'],['start','start','datetime','required'],['end','end','datetime','required']])</xsl:with-param>
					</xsl:call-template>	
				</td>
				<td>
					<xsl:call-template name="actionButton">
						<xsl:with-param name="text">Cancel</xsl:with-param>
						<xsl:with-param name="href">javascript:closeView()</xsl:with-param>
					</xsl:call-template>	
				</td>
			</tr>
			</table>
		</div>
	</div>
</xsl:template>


<!-- edit -->

<xsl:template match="activityEdit">
	<xsl:call-template name="windowhead">
		<xsl:with-param name="title">EditActivity</xsl:with-param>
	</xsl:call-template>
	<div class="windowcontent top">
		<form id="editAct" action="edit.jwa" method="post">
			<xsl:apply-templates select="activityItem"/>
		</form>
	</div>
	<div class="windowcontent bottom">
		<div class="buttons">
			<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<xsl:if test="activityItem/@editPriv">
					<td>
						<xsl:call-template name="actionButton">
							<xsl:with-param name="text">Save</xsl:with-param>
							<xsl:with-param name="href">javascript:setActionCheckFormSubmit('editAct','edit',[['name','Name','required'],['start','start','datetime','required'],['end','end','datetime','required'],['responsible','Responsible','receiverSelect','required']])</xsl:with-param>
						</xsl:call-template>	
					</td>
				</xsl:if>
				<xsl:if test="not(activityItem/@editPriv)">
					<td>
						<xsl:call-template name="actionButton">
							<xsl:with-param name="text">Save</xsl:with-param>
							<xsl:with-param name="href">javascript:setActionSubmit('editAct','edit')</xsl:with-param>
						</xsl:call-template>	
					</td>
				</xsl:if>
				<td>
					<xsl:call-template name="actionButton">
						<xsl:with-param name="text">Cancel</xsl:with-param>
						<xsl:with-param name="href">javascript:closeView()</xsl:with-param>
					</xsl:call-template>	
				</td>
			</tr>
			</table>
		</div>
	</div>
</xsl:template>

<xsl:template match="activityEdit/activityItem">
	<input type="hidden" name="id" value="{@id}"/>
	<div class="item">
	
		<!-- edit privs -->
		<xsl:if test="@editPriv">
			<span class="form heading block">name</span>
			<input class="form block dynamic" type="text" name="name" value="{@name}"/>
			<div>&#160;</div>
			<span class="form heading block">description</span>
			<xsl:call-template name="textarea">
				<xsl:with-param name="text" select="."/>
				<xsl:with-param name="class" select="'form dynamic'"/>
				<xsl:with-param name="name" select="'description'"/>
				<xsl:with-param name="style" select="'height:100px'"/>
			</xsl:call-template>
			<div>&#160;</div>
			<span class="form heading block">start</span>
			<div>
				<input class="form date" type="text" name="start" value="{@start}"/>&#160;<input class="form time" type="text" name="start_time" value="{@start_time}"/>
			</div>
			<span class="form heading block">end</span>
			<div>
				<input class="form date" type="text" name="end" value="{@end}"/>&#160;<input class="form time" type="text" name="end_time" value="{@end_time}"/>
			</div>
			<div>&#160;</div>
		</xsl:if>
		
		<!-- no edit privs -->
		<xsl:if test="not(@editPriv)">
			<span class="bold block"><xsl:value-of select="@name"/></span>
			<div style="padding-top:4px;">
				<span class="italic nobr"><xsl:value-of select="@start"/><xsl:value-of select="' - '"/><xsl:value-of select="@end"/></span>
			</div>
			<div class="item main">
				<xsl:call-template name="linebreak">
					<xsl:with-param name="text" select="."/>
				</xsl:call-template>
			</div>
			<div>&#160;</div>
		</xsl:if>
		
		<table cellpadding="0" cellspacing="0" border="0">
		<!-- update privs -->
		<xsl:if test="@updatePriv">
			<tr>
				<td class="table heading" align="right"><span class="table heading">status</span></td>
				<td width="100%">
					<xsl:call-template name="statusSelector">
						<xsl:with-param name="name">status</xsl:with-param>
						<xsl:with-param name="selected"><xsl:value-of select="@status"/></xsl:with-param>
					</xsl:call-template>
				</td>
			</tr>
		</xsl:if>
		<!-- no update privs -->
		<xsl:if test="not(@updatePriv)">
			<tr>
				<td colspan="2">
					<xsl:call-template name="statusImage">
						<xsl:with-param name="status"><xsl:value-of select="@status"/></xsl:with-param>
						<xsl:with-param name="overdue"><xsl:value-of select="@overdue"/></xsl:with-param>
					</xsl:call-template>
				</td>
			</tr>
		</xsl:if>
		
		<tr><td colspan="2">&#160;</td></tr>

		<!-- edit privs -->
		<xsl:if test="@editPriv">
			<tr>
				<td colspan="2">
					<xsl:apply-templates select="receiverSelect">
						<xsl:with-param name="selected">Responsible</xsl:with-param>
						<xsl:with-param name="notSelected">&#160;</xsl:with-param>
					</xsl:apply-templates>
				</td>
			</tr>
		</xsl:if>
		
		<!-- no edit privs -->
		<xsl:if test="not(@editPriv)">
			<tr>
				<td class="table heading" valign="top" align="right"><span class="table heading">Responsible</span></td>
				<td>
					<table cellpadding="0" cellspacing="0" border="0">
						<xsl:for-each select="receiverSelect/userItem">
							<xsl:if test="@selected">
								<tr>
									<td><xsl:value-of select="@name"/></td>
								</tr>
							</xsl:if>
						</xsl:for-each>
					</table>
				</td>
			</tr>
		</xsl:if>
		</table>
	</div>
</xsl:template>

</xsl:stylesheet>





