<?php

define('ReposWeb', dirname(dirname(__FILE__)).'/');
define('ROOT', dirname(ReposWeb).'/');

require(ReposWeb."conf/Command.class.php" );

$v = array();

# tag replaced by build script
$v['releaseversion'] = '1.3';

# info from svn if the application is checked out
$v['system'] = array();
$v['system']['repos-web'] = getSvnInfo(ReposWeb);

$v['plugins'] = array();
$plugins = getSubfolders(ROOT.'repos-plugins/');
foreach ($plugins as $p) {
	$v['plugins'][$p] = getSvnInfo(ROOT.'repos-plugins/'.$p.'/');
}

// Repos services

$v['open'] = array();
$plugins = getSubfolders(ReposWeb.'open/');
foreach ($plugins as $p) {
	$v['open'][$p] = getSvnInfo(ReposWeb.'open/'.$p.'/');
}

$v['edit'] = array();
$plugins = getSubfolders(ReposWeb.'edit/');
foreach ($plugins as $p) {
	$v['edit'][$p] = getSvnInfo(ReposWeb.'edit/'.$p.'/');
}

//header('Content-Type: text/plain');
//print_r($v);

require(ReposWeb.'conf/Presentation.class.php');
$p = Presentation::getInstance();
$p->assign_by_ref('v', $v);
$p->display();

/**
 * @param $path absolute path, for system safety path should not come from user input
 * @return array svn info from webapp path, false if not a working copy
 */
function getSvnInfo($path) {
	$svn = new Command('svn');
	$svn->addArgOption('info');
	$svn->addArgOption('--xml');
	$svn->addArg($path);
	if ($svn->exec()) {
		return false;
	}
	$output = $svn->getOutput();
	$info = _parseInfoXml($output);
	if ($info['url'] && preg_match('/.*(trunk|branches\/[^\/]+|tags\/[^\/]+).*/', $info['url'], $branch)) {
		$info['branch'] = $branch[1];
	}
	unset($info['url']);
	unset($info['kind']);
	unset($info['path']);
	unset($info['name']);
	unset($info['author']);
	unset($info['size']);
	return $info;
}

// borrowed from SvnOpenFile
/**
 * For folders
 * @param $xmlArray
 * @return unknown_type
 */
function _parseInfoXml($xmlArray) {
	$parsed = array();
	$patternsInOrder = array(
		'kind' => '/kind="([^"]+)"/',
		'path' => '/path="([^"]+)"/',
		'url' => '/<url>([^<]+)</', // need something between the two revision= because we should read the last one
		'revision' => '/revision="(\d+)"/',
		'author' => '/<author>([^<]+)</',
		'date' => '/<date>([^<]+)</',
	);
	list($n, $p) = each($patternsInOrder);
	for ($i=0; $i<count($xmlArray); $i++) {
		if (preg_match($p, $xmlArray[$i], $matches)) {
			$parsed[$n] = $matches[1];
			if(!(list($n, $p) = each($patternsInOrder))) break;
		}
	}
	$parsed['name'] = $parsed['path']; // looks like this is only the name
	$parsed['size'] = null;
	return $parsed;
}

/**
 * Get files and subdirectories in directory.
 * @param directory Path to check
 * @param startsWith Optional. Include only names that start with this prefix.
 * @return Filenames as array sorted alpabetically
 */
function getSubfolders($d) {
	if ( ! file_exists($d) )
	trigger_error( "Folder $d does not exist", E_USER_ERROR);
	$filelist = array();
	if ($dir = opendir($d)) {
		while (false !== ($file = readdir($dir))) {
			if ( $file != ".." && !strBegins($file,'.') && is_dir($d.$file) ) {
				$filelist[] = $file;
			}
		}
		closedir($dir);
	} else {
		trigger_error( "Folder $d could not be opened", E_USER_ERROR);
	}
	asort($filelist);
	return $filelist;
}
?>
