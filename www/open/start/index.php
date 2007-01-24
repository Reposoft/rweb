<?php

require(dirname(dirname(dirname(__FILE__))).'/account/login.inc.php');
require(dirname(__FILE__).'/RepositoryTree.class.php');
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');

/**
 * Hide "projects" that have no tools,
 * because they are probably only extra entries in the ACL
 *
 * @param RepositoryEntryPoint $entrypoint the ACL entry model
 * @todo Should we hide everything that is already inside a tool?
 */
function shouldShow($entrypoint) {
	return count($entrypoint->getTools()) > 0;
}

// if the user logged in directly to the repository, we need the cookie to be set
if (!isLoggedIn()) {
	header("Location: /?login");
	exit;
}

// read the ACL and create a tree for the user
$user = getReposUser();
$acl = getConfig('admin_folder').getConfig('access_file');
if (!is_file($acl)) {
	trigger_error("Can not read Access Control List", E_USER_ERROR);
}
$tree = new RepositoryTree($acl, $user);

// don't know why this is here
$repo = getRepository();
if (empty($repo)) trigger_error("Can not get repository url", E_USER_ERROR);

$entrypoints = array_filter($tree->getEntryPoints(), 'shouldShow');

$p = Presentation::getInstance();
$p->addStylesheet('repository/repository.css');
$p->assign('userfullname',$user);
$p->assign('repo',$repo);
$p->assign('entrypoints',$entrypoints);
//$p->display($p->getLocaleFile(dirname(__FILE__) . '/index-trunks')); // goes directly to trunk
$p->display();

?>