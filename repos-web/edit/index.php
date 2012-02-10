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
$file = new SvnOpenFile(getTarget(), $revisionRule->getValue());
// use svn on the file so we check authentication befor presentation starts
$file->_read();
if ($file->isFolder()) {
	trigger_error('This service is available for files only, got target ' + getTarget(), E_USER_ERROR);
}

$p = Presentation::getInstance();
$p->assign_by_ref('file', $file);
// for links to other operations we use the original parameters
$p->assign('target', getTarget());
// display a short log for the file on the edit page
require(dirname(dirname(__FILE__)).'/open/getlog.php');
$p->assign('log', getLog(getTargetUrl()));
// all set
$p->display();

?>
