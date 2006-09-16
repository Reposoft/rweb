<?php

require(dirname(dirname(dirname(__FILE__))).'/account/login.inc.php');
require(dirname(__FILE__).'/RepositoryTree.class.php');
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');

if (!isLoggedIn()) {
	trigger_error("This is for logged in users only");
	exit;
}
$user = getReposUser();
$acl = getConfig('admin_folder').'/'.getConfig('access_file');
if (!is_file($acl)) {
	trigger_error("Can not read Access Control List");
	exit;
}
$tree = new RepositoryTree($acl, $user);

$repo = getRepositoryUrl();
if (empty($repo)) {
	trigger_error("Can not get repository url");
	exit;
}

$p = new Presentation();
$p->assign('userfullname',$user);
$p->assign('repo',$repo);
$p->assign('entrypoints',$tree->getEntryPoints());
$p->display();

?>