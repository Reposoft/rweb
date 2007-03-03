<?php
/**
 * 
 * @package open
 */
require("../../conf/Presentation.class.php" );
require("../SvnOpenFile.class.php" );
addPlugin('syntax');
addPlugin('calendar');
addPlugin('password');

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();
$file = new SvnOpenFile(getTarget(), $rev);
// Error handling for non existing file is in the template
// If that's a good idea, then probably error handling for big file should be there too

// show
$p = Presentation::getInstance();
$p->assign('target', getTarget());
$p->assign('rev', $rev);
if ($file->getSize() > 102400) {
	$p->showError('The file is bigger than 100kb. Please open the file directly instead of viewing it in a page.',
		'File is too big for this page.');	
}
$p->assign_by_ref('file', $file);
$p->display();

?>