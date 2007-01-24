<?php
/**
 * List the available tools for a project.
 * A project in the repository is defined as a folder that contains a "trunk/" folder.
 * @deprecated this functionality is now found in ../start/ so the page is never needed
 */

if (!function_exists('getRepository')) require('../../conf/repos.properties.php');
if (!function_exists('getTarget')) require('../../account/login.inc.php');
if (!class_exists('ServiceRequest')) require('../ServiceRequest.class.php');
require('../../conf/Presentation.class.php');
require('../start/RepositoryTree.class.php');

$tools = getRepositoryConventionsForTools(getProjectName());
$existing = array_filter($tools, 'toolExists');

$p = Presentation::getInstance();
$p->assign('tools', $existing);
$p->assign('project', getProjectName());
$p->assign('repo', getRepository());
$p->display();

function toolExists($path) {
	$toplevel = getContents();
	if (in_array($path, $toplevel)) return true;
	if (strAfter($path, '/')) return urlExists(getRepository().getProject().$path);
	return false;
}

function urlExists($url) {
	$get = new ServiceRequest($url,array());
	$get->exec();
	return $get->isOK();
}

/**
 * @return String project start path (not including 'trunk')  with leading and trailing slash
 */
function getProject() {
	static $target = null;
	if (!$target) $target = getTarget();
	if (!$target) trigger_error('The "target" parameter must specify a project path.', E_USER_ERROR);
	return $target;
}

/**
 * @see RepositoryEntryPont::getDisplayName()
 */
function getProjectName() {
	static $name = null;
	if (!is_null($name)) return $name;
	preg_match('/.*\/([^\/]+)\//', getProject(), $matches);
	$name = $matches[1];
	return $name;
}

function getContents() {
	static $contents = null;
	if (is_null($contents)) $contents = getRepositoryFolderContents(getProject());
	return $contents;
}

?>