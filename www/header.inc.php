<?php

// Note that conf/repos.properties must be included before this file is included to enable fallback to config values

/**
 * It is assumed that all pages include this file, so we fix the headers here
 */
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0
header("Pragma: no-cache");

// ------ url resolution functions -----

function getReferer() {
    if (isset($_SERVER['HTTP_REFERER'])) return $_SERVER['HTTP_REFERER'];
    return false;
}

/**
 * @return target in repository from query paramters WITH tailing slash if no file
 */
function getTarget() {
    // invalid parameters -> empty string
    if(!isset($_GET['path'])) return '';
    $path = $_GET['path'];
    // end with slash
    $path = rtrim($path,'/').'/';
    // append filename if specified
    if(isset($_GET['file'])) $path .= $_GET['file'];
    return $path;
}

/**
 * Repository is resolved using HTTP Referrer with fallback to settings.
 * To find out where root is, query paramter 'path' must be set.
 * @return Root url of the repository for this request
 */
function getRepositoryUrl() {
	if (isset($_GET['repo'])) {
		return $_GET['repo'];
	}
    $ref = getReferer();
    if ($ref && isset($_GET['path'])) {
		$repo = substr($ref,0,strpos($ref,$_GET['path']));
		if (count($repo)>0) return $repo; 
    }
    if(function_exists('getConfig')) {
    	return getConfig('repo_url');
	} else {
		return false;	
	}
}

/**
 * Target url is resolved from query parameters
 *  'repo' (fallback to getRepositoryUrl) = root url
 *  'path' = path from repository root
 *  'file' (omitted if not found) = filename inside path
 * @return Full url of the file or directory this request targets
 */
function getTargetUrl() {
    return getRepositoryUrl() . getTarget();
}

?>
