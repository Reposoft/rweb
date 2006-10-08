<?php

require(dirname(dirname(dirname(__FILE__))).'/account/login.inc.php');
require(dirname(__FILE__).'/RepositoryTree.class.php');
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');

// currently this page can not handle paths inside trunk, because it adds /trunk to all entry paths
function shouldShow($entrypoint) {
	return strpos($entrypoint->getPath(), '/trunk/') === false;
}

if (!isLoggedIn()) {
	trigger_error("This is for logged in users only", E_USER_WARNING);
}
$user = getReposUser();
$acl = getConfig('admin_folder').getConfig('access_file');
if (!is_file($acl)) {
	trigger_error("Can not read Access Control List", E_USER_ERROR);
}
$tree = new RepositoryTree($acl, $user);

$repo = getRepositoryUrl();
if (empty($repo)) {
	trigger_error("Can not get repository url", E_USER_ERROR);
}

$entrypoints = array_filter($tree->getEntryPoints(), 'shouldShow');

$p = new Presentation();
$p->addStylesheet('repository/repository.css');
$p->assign('userfullname',$user);
$p->assign('repo',$repo);
$p->assign('entrypoints',$entrypoints);
$p->display();

?>