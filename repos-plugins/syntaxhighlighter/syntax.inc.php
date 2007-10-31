<?php
/**
 *
 *
 * @package plugins
 */
define('REPOS_SYNTAX_DP_PATH', 'lib/dpsyntax/dp.SyntaxHighlighter/');
function syntax_getBrushes() {
	return array(
	'/(.*)/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shCore.js',
	'/\.(php)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushPhp.js',
	'/\.(js)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushJScript.js',
	'/\.(java)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushJava.js',
	'/\.(vba?)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushVb.js', // what filetypes for VB?
	'/\.(sql)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushSql.js',
	'/\.(py)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushPython.js',
	'/\.(rb)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushRuby.js',
	'/\.(cpp)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushCpp.js',
	'/\.(css)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushCss.js',
	'/\.(xml|htm|html|xsl|jsp)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushXml.js',
	'/\.(txt)\b/' => 'plugins/syntax/shBrushWiki.js',
	'/\.(accs)\b/' => 'plugins/syntax/shBrushAcl.js',
	'/\.(htp)\b/' => 'plugins/syntax/shBrushHtp.js',
	'/open\/(diff)\/\?/' => 'plugins/syntax/shBrushDiff.js'
	);
}

/*
 * <script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushCSharp.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushPhp.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushJScript.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushJava.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushVb.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushSql.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushXml.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushDelphi.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushPython.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushRuby.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushCss.js"></script>
<script class="javascript" src="../../lib/dpsyntax/dp.SyntaxHighlighter/Scripts/shBrushCpp.js"></script>
 * 
 */

function syntax_isDpsyntaxInstalled() {
	return file_exists(dirname(dirname(dirname(__FILE__))).'/lib/dpsyntax/dp.SyntaxHighlighter/');
}

function syntax_getHeadTags($webapp) {
	if (!syntax_isDpsyntaxInstalled()) return array();
	$REPOS_SYNTAX_BRUSH = syntax_getBrushes();
	$load = array(
		'<link type="text/css" rel="stylesheet" href="'.$webapp.'plugins/syntax/syntax.css"></link>'
	);
	$url = $_SERVER['REQUEST_URI'];
	foreach($REPOS_SYNTAX_BRUSH as $regexp => $script) {
		if (preg_match($regexp, $url)) {
			$load[] = '<script type="text/javascript" src="'.$webapp.$script.'"></script>';
		}
	}
	$load[] = '<script type="text/javascript" src="'.$webapp.'plugins/syntax/syntax.js"></script>';
	return $load;
}

?>
