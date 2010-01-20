<?php
/**
 * Uses SvnOpenFile->sendInlineHtml() to display file contents in textarea.
 * @package open
 */
require("../../conf/Presentation.class.php" );
require("../SvnOpenFile.class.php" );

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();
$file = new SvnOpenFile(getTarget(), $rev);
// Error handling for non existing file is in the template
// If that's a good idea, then probably error handling for big file should be there too

// show
$p = Presentation::getInstance();
$p->assign('target', getTarget());
$p->assign('rev', $rev);
if ($file->getSize() > REPOS_TEXT_MAXSIZE) {
	$p->showError('The file is bigger than '.REPOS_TEXT_MAXSIZE_STR.
		'. Please open the file directly instead of viewing it in a page.',
		'File is too big for this page.');
}
$p->assign_by_ref('file', $file);
$p->display();

?>