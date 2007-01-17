<?php
/**
 *
 *
 * @package plugins
 */
 
function syntax_getHeadTags($webapp) {
	return array(
		'<link type="text/css" rel="stylesheet" href="'.$webapp.'plugins/syntax/syntax.css"></link>',
		'<script type="text/javascript" src="'.$webapp.'plugins/syntax/syntax.js"></script>'
	);
}

?>
