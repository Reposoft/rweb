<?php
/**
 * Shows a list of options on how to edit the file.
 * @package edit
 */
require(dirname(dirname(__FILE__)).'/conf/Presentation.class.php');
require(dirname(dirname(__FILE__)).'/open/SvnOpenFile.class.php');

// old behaviour, forward to an action
if(isset($_GET['action'])) {
	header('Location: '.getWebapp().'edit/'.$_GET['action'].'/?'.$_SERVER['QUERY_STRING']);
}

// new behaviour, list the user's options
$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();
$file = new SvnOpenFile(getTarget(), $rev);

$p = Presentation::getInstance();
$p->assign_by_ref('file', $file);
// for links to other operations we use the original parameters
$p->assign('rev', $rev);
$p->assign('target', getTarget());
// all set
$p->display();

?>