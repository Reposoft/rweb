<?php
/**
 * Personalized repository entry (c) 2006-2007 Staffan Olsson www.repos.se 
 */
require(dirname(dirname(dirname(__FILE__))).'/account/login.inc.php');
require(dirname(dirname(dirname(__FILE__))).'/account/RepositoryTree.class.php');
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');

// need to add at least one plugin to get the ResourceId script loaded
addPlugin('dateformat');

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
// TODO can this redirect be avoided now that service request forwards authentication?
// - not if we want authentication to be server wide
// This page is special because it has no "target" that we can use to check is login is needed
// - this means we can't use targetLogin()
if (!isLoggedIn()) {
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
}

// read the ACL and create a tree for the user
$user = getReposUser();
$repo = getRepository();
$acl = getAccessFile();
if (!$acl || !is_file($acl)) {
	// TODO add another method to list start page entries that is based on current access rights from a readable root folder
	// maybe simplified by using the new svn list --depth in subverison 1.5
	// Might have different subclasses of the RepositoryTree interface
	// Until that's implemented, use subversion index as startpage instead, if access is allowed
	$s = new ServiceRequest($repo.'/');
	if ($s->exec()==200) {
		header("Location: ".asLink($repo).'/');
		exit;
	}
	// no acl, no read access to repo root -> give up
	trigger_error("Can not show start page because it requires an Access Control List or read access to repository root", E_USER_ERROR);
}
$tree = new RepositoryTree($acl, $user);

$entrypoints = array_filter($tree->getEntryPoints(), 'shouldShow');
if (count($entrypoints)==0) {
	trigger_error('The access control file does not list any projects for this login. '. // no folders that match Subverson or Repos project conventions
		'Try <a href="'.$repo.'/">repository root</a>.', E_USER_ERROR);
	// TODO make a template instead of an error message, like this...
	$p = Presentation::getInstance();
	$p->assign('entrypoints',$entrypoints); // and use the paths directly
	$p->display($p->getLocaleFile(dirname(__FILE__) . '/index-notools'));
}

$p = Presentation::getInstance();
$p->addStylesheet('repository/repository.css');
$p->assign('repository', $repo);
$p->assign('denied', isset($_GET['denied']) ? $_GET['denied'] : false);
$p->assign('userfullname',$user);
$p->assign('entrypoints',$entrypoints);
//$p->display($p->getLocaleFile(dirname(__FILE__) . '/index-trunks')); // goes directly to trunk
$p->display();

?>