<?php
/**
 * Subversion hook scripts must be called from a location
 * that can be derived from the repository path.
 * Admin folder is the best place for that,
 * and here we can look in repos.properties to get the URL.
 * 
 * This script should be minimal, because output is not read by anyone.
 *
 * @package conf
 */

// set custom url, with trailing slash to repos installation
$repos_web = false;
// if url is not specified, read from repos.properties
if (!$repos_web) {
	$conf = parse_ini_file(dirname(__FILE__).'/repos.properties', false);
	$repos_web = $conf['repos_web']; 
}

// call the admin post-commit function
$command = $argv[1];
$path = rawurlencode($argv[2]);
$rev = $argv[3];
$url = $repos_web."admin/$command/?repopath=$path&rev=$rev&".WEBSERVICE_KEY.'=text';

$fh = fopen($url, 'r');
$contents = fread($fh, 1024);
fclose($fh);

?>
