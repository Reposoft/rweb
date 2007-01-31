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
	'/\.(xml|htm|html)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushXml.js',
	'/\.(css)\b/' => REPOS_SYNTAX_DP_PATH . 'Scripts/shBrushCss.js',
	'/\.(txt)\b/' => 'plugins/syntax/shBrushWiki.js',
	'/open\/(diff)\/\?/' => 'plugins/syntax/shBrushDiff.js'
	);
}

function syntax_getHeadTags($webapp) {
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
