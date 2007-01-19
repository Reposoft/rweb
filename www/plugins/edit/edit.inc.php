<?php
/**
 * WYSIWYG editing plugin
 *
 * @package plugins
 */

function edit_getHeadTags($webapp) {
	return array(
		'<script language="javascript" type="text/javascript" src="'.$webapp.'lib/tinymce/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>',
		'<script type="text/javascript" src="'.$webapp.'plugins/edit/edit.js"></script>'
	);
}
 

?>
