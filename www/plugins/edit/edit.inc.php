<?php
/**
 * WYSIWYG editing plugin
 *
 * @package plugins
 */

/**
 * Shared between edit/file/ and edit/upload/
 * @package edit
 */
class EditTypeRule extends Rule {
	function valid($value) {
		return ($value = 'upload' 
			|| $value == 'txt' 
			|| $value == 'html');
	}
}

function edit_getHeadTags($webapp) {
	return array(
		'<script language="javascript" type="text/javascript" src="'.$webapp.'lib/tinymce/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>',
		'<script language="javascript" type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	theme : "simple",
	editor_selector : "html",
	content_css : "/documents.css"
});
</script>'.
		'<script type="text/javascript" src="'.$webapp.'plugins/edit/edit.js"></script>'
	);
}
 

function editWriteNewVersion_html($postedText, $destinationFile, $type) {
	$postedText = preg_replace(
		'/<(p|br|h1|h2|h3|ul|ol|li)/',
		"\n<$1",
		$postedText
	);
	// use plaintext write
	editWriteNewVersion_txt($postedText, $destinationFile, $type);
}

?>
