<?php
/**
 * Uses SvnOpenFile->sendInlineHtml() to display file contents in textarea.
 * @package open
 */
require("../../conf/Presentation.class.php" );
require("../SvnOpenFile.class.php" );
require("./images.inc.php");

$revisionRule = new RevisionRule();
$file = new SvnOpenFile(getTarget(), $revisionRule->getValue());
// Error handling for non existing file is in the template
// If that's a good idea, then probably error handling for big file should be there too

// show
$p = Presentation::getInstance();
$p->assign('target', getTarget());
$p->assign_by_ref('file', $file);

if (reposViewGetImageUrl($file)) {
	$p->display($p->getLocaleFile(dirname(__FILE__).'/image'));
} else {
	if ($file->getSize() > REPOS_TEXT_MAXSIZE) {
		$p->showError('The file is bigger than '.REPOS_TEXT_MAXSIZE_STR.
			'. Please open the file directly instead of viewing it in a page.',
			'File is too big for this page.');
	}
	$p->display();
}

?>