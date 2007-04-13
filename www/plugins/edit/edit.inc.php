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

function edit_isTinyMCEInstalled() {
	return file_exists(dirname(dirname(dirname(__FILE__))).'/lib/tinymce/tinymce/');	
}

function edit_getHeadTags($webapp) {
	if (!edit_isTinyMCEInstalled()) return array();
	return array(
		'<script language="javascript" type="text/javascript" src="'.$webapp.'lib/tinymce/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js"></script>',
		'<script language="javascript" type="text/javascript">
tinyMCE_GZ.init({
	plugins : "style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
	themes : "advanced",
	languages : "en",
	disk_cache : true,
	debug : false
});
</script>',
		'<script type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	theme_advanced_buttons1 : "cut,copy,paste,separator,undo,redo,separator,bold,italic,underline,separator,bullist,numlist,separator,formatselect",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_blockformats : "h1, h2, h3, p",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "center",
	entity_encoding : "raw",
	content_css : "/home/documents.css",
	editor_selector : "none"
});
</script>',
		'<script type="text/javascript" src="'.$webapp.'plugins/edit/edit.js"></script>'
	);
}
 
/**
 * Make a text document in HTML format suitable for simple version control.
 * 
 * TODO If the HTML is posted as only body-contents, and a previous version exists,
 * the document headers will be copied from the previous version.
 * If there is no previous headers, standard XHTML 1.0 strict headers will be created.
 * 
 * This function always uses line-feed-only newlines.
 * Most HTML editors on windows will accept that.
 *
 * @param String $postedText the document contents as valid XHTML 1.0 strict
 * @param String $destinationFile the current file
 */
function editWriteNewVersion_html(&$postedText, $destinationFile) {
	// texts coming from smarty have only body contents
	if (!preg_match('/<html.*<body.*/ms', $postedText)) {
		// indent only documents edited with the online html editor
		// (not only new documents, but currently updates end up here too)
		$postedText = editIndentHtmlDocument($postedText);
		// TODO use existing html if new version? Use a templates/ file?
		// Put the contents in the Repos template
		$postedText = editGetNewDocument($postedText);
	}
	
	// use plaintext write
	editWriteNewVersion_txt($postedText, $destinationFile);
}

/**
 * Indent according to Repos rules, so line-based version control works.
 * - Only newlines, no indentations.
 * - Line break after sentence.
 *
 * @param String $html xhtml body contents (should be valid xml).
 * 	Passed by reference for memory efficiency.
 * 	Tagnames should always be lowercase.
 */
function editIndentHtmlDocument(&$html) {
	// newline before selected start tags
	$html = preg_replace(
		'/(?!\n)\s*<(p|br|h1|h2|h3|ul|ol|\/ul|\/ol|li)/',
		"\n<$1",
		$html
	);
	// newline after text sentences
	$html = preg_replace('/([\w;][\.\?\!\:])(\s)?(\s*)((?(2)&?[A-Z0-9]|<))/', 
		"$1$2$3\n$4",
		$html);
	return $html;
}

function editGetNewDocument($bodyContents) {
	$template = dirname(__FILE__).'/template_en.html';
	$p = smarty_getInstance();
	$p->assign('title', 'text');
	$p->assign('generator', 'Repos');
	$p->assign('body', $bodyContents);
	return $p->fetch($template);
}

?>
