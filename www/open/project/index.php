<?php
/**
 * List the available tools for a project.
 * A project in the repository is defined as a folder that contains a "trunk/" folder.
 */

if (!function_exists('getRepository')) require('../../conf/repos.properties.php');
if (!function_exists('getTarget')) require('../../account/login.inc.php');
if (!class_exists('ServiceRequest')) require('../ServiceRequest.class.php');
require('../../conf/Presentation.class.php');

// tool id => resource to check
$tools = array(
	'files' => 'trunk/',
	'branches' => 'branches/',
	'tasks' => 'tasks/',
	'news' => 'messages/news.xml',
	'calendar' => 'calendar/'.getProjectName().'.ics',
	'nonexisting' => 'dummy/' //just testing
	);

$existing = array_filter($tools, 'toolExists');

$p = new Presentation();
$p->assign('tools', $existing);
$p->assign('project', getProjectName());
$p->assign('repo', getRepository());
$p->display();

function toolExists($path) {
	$toplevel = getContents();
	if (in_array(rtrim($path,'/'), $toplevel)) return true;
	if (strAfter($path, '/')) return urlExists(getProjectUrl().$path);
	return false;
}

function urlExists($url) {
	$get = new ServiceRequest($url,array());
	$get->exec();
	return $get->isOK();
}

/**
 * @return String project start URL (containing trunk folder)  with leading and trailing slash
 */
function getProject() {
	static $target = null;
	if (!$target) $target = getTarget();
	if (!$target) trigger_error('The "target" parameter must specify a project path.', E_USER_ERROR);
	return $target;
}

function getProjectName() {
	static $name = null;
	if (!is_null($name)) return $name;
	preg_match('/.*\/([^\/]+)\//', getProject(), $matches);
	$name = $matches[1];
	return $name;
}

function getProjectUrl() {
	static $url = null;
	if (is_null($url)) $url = getRepository() . getProject();
	return $url;
}

function getContents() {
	static $contents = null;
	if (is_null($contents)) $contents = getContentsFromRepositoryXml(getProjectUrl());
	return $contents;
}

function getContentsFromRepositoryXml($url) {
	$list = new ServiceRequest($url,array());
	$list->exec();
	if ($list->getResponseType()!='text/xml') trigger_error("Repository URL $url did not deliver xml.", E_USER_ERROR);
	preg_match_all('/dir\sname="([^"]+)"/', $list->getResponse(), $matches);
	return $matches[1];
}

?>