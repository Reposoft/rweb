<?php
/**
 * Repos start of navigation (c) 2006-2007 Staffan Olsson www.repos.se 
 * Should provide a starting point for all users regardless of access level.
 * Used as link into Repos Web and as exit were the user might be lost, such as error pages.
 */
require(dirname(dirname(dirname(__FILE__))).'/account/login.inc.php');
require(dirname(dirname(dirname(__FILE__))).'/account/RepositoryTree.class.php');
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');

// need to add at least one plugin to get the ResourceId script loaded
addPlugin('dateformat');

/**
 * Hide paths that are inside shown paths.
 * Note that this makes ordering of acl entries important,
 * the reason mainly being the original design of this function.
 * 
 * A subentry will be shown only if declared before the parent in the ACL
 * - a feature only if the ACL authors know about it.
 *
 * @param RepositoryEntryPoint $entrypoint the ACL entry model
 * @todo Should we hide everything that is already inside a tool?
 */
function shouldShow($entrypoint) {
	static $shown = array();
	$path = $entrypoint->getPath();
	foreach ($shown as $s) {
		if (strBegins($path, $s)) return false;
	}
	//root should not affect the decision because it is parent to everything
	if ($path != '' && $path != '/') {
		$shown[] = $path;
	}
	return true;
}

/**
 * Redirects to repository root if the user has read access there
 * and exits.
 */
function repos_start_tryRepoRoot() {
	$repo = getRepository();
	$s = new ServiceRequest($repo.'/');
	// For status 401 ServiceRequest should transparently request authentication
	// Repository access ok for user, redirect
	if ($s->exec() == 200) {
		header("Location: ".asLink($repo).'/');
		exit;
	}
}

// if the user logged in directly to the repository, we need the cookie to be set
// TODO can this redirect be avoided now that service request forwards authentication?
// - not if we want authentication to be server wide
// This page is special because it has no "target" that we can use to check is login is needed
// - this means we can't use targetLogin()
function repos_start_authenticate() { if (!isLoggedIn()) {
	if (isset($_COOKIE[USERNAME_KEY])) {
		// for browsers that don't automatically send credentials to pages below login url
		// Required in Safari
		$rootrealm = getAuthName(getRepository());
		if ($rootrealm) {
			askForCredentials($rootrealm);
		} else {
			// Account is set but repository does not require authentication
			// How do we handle this? Try auth urls like account/login/ does?
			trigger_error('Account could not be validated. '.
				'Try <a href="'.getRepository().'/">repository root</a>.', E_USER_ERROR);
		}
	} else {
		// most browsers would send credentials without first being proptet with Authentication Requered
		// Having this redirect here means that start page requires login
		// - an alternative would be to parse the acl with user "*"
		header("Location: /?login");	
	}
	exit;
}}

// read the ACL and create a tree for the user
$user = getReposUser();
$acl = getAccessFile();
if (!$acl || !is_file($acl)) {
	// TODO add another method to list start page entries that is based on current access rights from a readable root folder
	// maybe simplified by using the new svn list --depth in subverison 1.5
	// Might have different subclasses of the RepositoryTree interface
	// Until that's implemented, use subversion index as startpage instead, if access is allowed
	repos_start_tryRepoRoot();
	// no acl, no read access to repo root -> give up
	trigger_error("Can not show start page because it requires an Access Control List or read access to repository root", E_USER_ERROR);
} else {
	// we do have an ACL, use the old login verification code
	repos_start_authenticate();
}
$tree = new RepositoryTree($acl, $user);

$entrypoints = array_filter($tree->getEntryPoints(), 'shouldShow');
if (count($entrypoints)==0) {
	repos_start_tryRepoRoot();
	trigger_error('The access control file does not list any projects for this login. '. // no folders that match Subverson or Repos project conventions
		'Links directly to allowed folders are required for access.', E_USER_ERROR);
	// TODO make a template instead of an error message, like this...
	$p = Presentation::getInstance();
	$p->assign('entrypoints',$entrypoints); // and use the paths directly
	$p->display($p->getLocaleFile(dirname(__FILE__) . '/index-notools'));
}

$p = Presentation::getInstance();
$p->addStylesheet('repository/repository.css');
$p->assign('repository', getRepository());
$p->assign('denied', isset($_GET['denied']) ? $_GET['denied'] : false);
$p->assign('userfullname',$user);
$p->assign('entrypoints',$entrypoints);
//$p->display($p->getLocaleFile(dirname(__FILE__) . '/index-trunks')); // goes directly to trunk
$p->display();

?>
