<?php
/**
 * 
 * @package open
 */
require("../../conf/Presentation.class.php" );
require("../SvnOpenFile.class.php" );
addPlugin('syntax');

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();
$file = new SvnOpenFile(getTarget(), $rev);

// show
$p = new Presentation();
$p->assign('target', getTarget());
$p->assign('rev', $rev);
if ($file->getSize() > 102400) {
	$p->showError('The file is bigger than 100kb. Please open the file directly instead of viewing it in a page.',
		'File is too big for this page.');	
}
$p->assign_by_ref('file', $file);
$p->display();

?>