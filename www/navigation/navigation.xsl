<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml" version="1.0" xml:lang="en">
    
    <xsl:output method="xml" indent="no"
                doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
    
    <!-- svnlayout settings, copied -->
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

    <!-- layout parameters from coordinator -->
    <xsl:param name="imageUrl"><xsl:value-of select="$iconsUrl"/>/tree/</xsl:param>
    <xsl:param name="jsUrl"><xsl:value-of select="$rurl"/>/js/</xsl:param>
    <!-- <xsl:param name="cssUrl" select="'css/'"/> -->
    <xsl:param name="iconwidth" select="'18'"/>
    <xsl:param name="iconheight" select="'14'"/>
    <!-- root of navigation tree xml -->
    <xsl:template match="navigation">
        <html>
            <head>
                <title>repos.se navigation</title>
                <link href="{$cssUrl}/navigation.css" rel="stylesheet" type="text/css"/>
                <script language="JavaScript" type="text/javascript" src="{$jsUrl}everywhere.js">&#160;</script>
                <script language="JavaScript" type="text/javascript" src="{$jsUrl}tree.js">&#160;</script>
            </head>
            <body bgcolor="#999999" marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">
                <xsl:apply-templates select="." mode="contents"/>
            </body>
        </html>
    </xsl:template>
    <!-- helper function -->
    <xsl:template name="maxSubtreeDepth">
        <xsl:param name="prootNode" select="."></xsl:param>
        <xsl:variable name="vLeaves" select="$prootNode//node()[not(node())]"/>
        <xsl:for-each select="$vLeaves">
            <xsl:sort select="count(ancestor::node())" data-type="number" order="descending"/>
            <xsl:if test="position() = 1">
                <xsl:value-of select="count(ancestor::node())"/>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>
    <!-- ****** unit tree ******* -->
    <xsl:template match="navigation" mode="contents">
        <xsl:param name="depth">
            <!-- get depth of tree -->
            <xsl:call-template name="maxSubtreeDepth"/>
        </xsl:param>
        <xsl:param name="columns">
            <xsl:value-of select="$depth + 1"/>
        </xsl:param>
        <table id="navigationTreeTable" width="180" border="0" cellpadding="0" cellspacing="0">
            <!-- logo and stuff -->
            <tr>
                <td colspan="{$columns}">
                    <xsl:text>repos.se</xsl:text>
                </td>
            </tr>
            <!-- header -->
            <tr>
                <td colspan="{$columns}" bgcolor="#FFFFFF">
                    <img alt="" src="{$imageUrl}whiteline180.gif" width="180" height="2" hspace="0" vspace="0"/>
                </td>
            </tr>
            <!-- first unit -->
            <tr id="treeRoot">
                <td class="treeIcon">
                    <img height="14" width="18" src="{$imageUrl}collection.gif" alt=""/>
                </td>
                <td class="treeText" colspan="{$columns - 1}">
                    <xsl:text>Avdelningar</xsl:text>
                </td>
            </tr>
            <!-- units -->
            <xsl:apply-templates select="unit">
                <xsl:with-param name="colspan">
                    <xsl:value-of select="$columns - 2"/>
                </xsl:with-param>
            </xsl:apply-templates>
            <!-- for test usage: reset state -->
            <tr>
                <td class="treeText" colspan="{$columns}">
                    <xsl:text>&#160;</xsl:text>
                    <a href="?treeReset=true" target="_self" style="color:#666666;text-decoration:none">[expand all]</a>
                </td>
            </tr>
            <!-- footer -->
            <tr>
                <xsl:call-template name="emptyIconCell">
                    <xsl:with-param name="emptyCount">
                        <xsl:value-of select="$columns - 1"/>
                    </xsl:with-param>
                </xsl:call-template>
                <td>
                    <img alt="">
                        <xsl:attribute name="src">
                            <xsl:value-of select="$imageUrl"/>w<xsl:value-of select="180 - ($columns - 1) * 18"/>
                            <xsl:value-of select="'.gif'"/>
                        </xsl:attribute>
                    </img>
                </td>
            </tr>
        </table>
        <script type="text/javascript"><![CDATA[ initNavigation(); ]]></script>
    </xsl:template>
    
    <!-- units recursively -->
    <xsl:template match="unit">
        <!-- input: tags/text before first cell in row -->
        <xsl:param name="indent"/>
        <!-- input: colspan of last cell -->
        <xsl:param name="colspan">1</xsl:param>
        <!-- derived -->
        <xsl:param name="childUnits">
            <xsl:value-of select="count(*)"/>
        </xsl:param>
        <xsl:param name="unitId">
            <xsl:value-of select="'unit'"/>
            <xsl:value-of select="@id"/>
        </xsl:param>
        <!-- numbers needed for calling tree script functions -->
        <xsl:param name="unitnumber">
            <xsl:number count="unit" from="navigationTree" level="any"/>
        </xsl:param>
        <xsl:param name="descendants">
            <xsl:value-of select="count(descendant::*)"/>
        </xsl:param>
        <!-- unitRow is one unitnumber+2 because root node renders 'overview' and 'personal' -->
        <xsl:param name="unitRow">
            <xsl:value-of select="$unitnumber + 2"/>
        </xsl:param>
        <!-- style class should be different for last child -->
        <xsl:variable name="indentClass">
            <xsl:if test="not(position()=last())">
                <xsl:value-of select="'treeSpace'"/>
            </xsl:if>
            <xsl:if test="position()=last()">
                <xsl:value-of select="'treeIcon'"/>
            </xsl:if>
        </xsl:variable>
        <!-- new row -->
        <tr id="{$unitId}">
            <!-- indent -->
            <xsl:value-of select="$indent" disable-output-escaping="yes"/>
            <!-- expand icon -->
            <td class="{$indentClass}">
                <xsl:if test="$childUnits = 0">
                    <xsl:call-template name="treeIcon">
                        <xsl:with-param name="image" select="'single.gif'"/>
                    </xsl:call-template>
                </xsl:if>
                <xsl:if test="$childUnits &gt; 0">
                    <xsl:call-template name="treeIcon">
                        <xsl:with-param name="image" select="'expanded.gif'"/>
                        <xsl:with-param name="onclick">javascript:clickUnit(<xsl:value-of
                                select="$unitRow"/>,<xsl:value-of select="$descendants"/>
                            <xsl:value-of select="',this);'"/>
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:if>
            </td>
            <!-- type icon -->
            <td class="treeIcon">
                <xsl:call-template name="treeIcon">
                    <xsl:with-param name="image">
                        <xsl:value-of select="@type"/>.gif</xsl:with-param>
                </xsl:call-template>
            </td>
            <!-- unit name -->
            <td class="treeText" colspan="{$colspan}">
                <a href="{$unitId}/bb/.jwa" target="main">
                    <xsl:attribute name="onClick">javascript:setVisibleTools(<xsl:apply-templates select="." mode="visibleTools"/>)</xsl:attribute>
                    <xsl:value-of select="@name"/>
                </a>
            </td>
            <!-- done -->
        </tr>
        <!-- childrem, if any -->
        <xsl:apply-templates select="unit">
            <xsl:with-param name="indent">
                <!-- add the old indentation tag + another level of escaped html tag -->
                <xsl:value-of select="$indent"/>
                <xsl:value-of select="'&lt;td class=&quot;'"/>
                <xsl:value-of select="$indentClass"/>
                <xsl:value-of select="'&quot;&gt; &lt;/td&gt;'"/>
            </xsl:with-param>
            <xsl:with-param name="colspan">
                <xsl:value-of select="$colspan - 1"/>
            </xsl:with-param>
        </xsl:apply-templates>
    </xsl:template>
    <!-- image icons -->
    <xsl:template name="treeIcon">
        <xsl:param name="image" select="'single.gif'"/>
        <xsl:param name="onclick"/>
        <img alt="" src="{$imageUrl}{$image}" width="{$iconwidth}" height="{$iconheight}">
            <xsl:if test="$onclick">
                <xsl:attribute name="onclick">
                    <xsl:value-of select="$onclick"/>
                </xsl:attribute>
            </xsl:if>
        </img>
    </xsl:template>
    <!-- generates 'emptyCount' empty cell(s) -->
    <xsl:template name="emptyIconCell">
        <xsl:param name="emptyCount">1</xsl:param>
        <td>
            <img alt="" src="{$imageUrl}w18.gif" width="18"/>
        </td>
        <xsl:if test="$emptyCount &gt; 1">
            <xsl:call-template name="emptyIconCell">
                <xsl:with-param name="emptyCount">
                    <xsl:value-of select="$emptyCount - 1"/>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>
    <!-- generate bitmask for which tools to show at the respective unit type -->
    <xsl:template match="unit" mode="visibleTools">
        <xsl:variable name="binary">
            <xsl:choose>
                <!-- all these must include tool 8 (128) because this number will be subtracted if user is not admin -->
                <xsl:when test="@type=24">129</xsl:when>
                <xsl:when test="@type=26">129</xsl:when>
                <xsl:when test="@type=25">129</xsl:when>
                <xsl:when test="@type=19">255</xsl:when>
                <xsl:when test="@type=17">191</xsl:when>
                <xsl:otherwise>255</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:if test="@admin">
            <xsl:value-of select="$binary"/>
        </xsl:if>
        <xsl:if test="not(@admin)">
            <xsl:value-of select="$binary - 128"/>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>