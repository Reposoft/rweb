<?php
/**
 * Defines the dependency to Repos Web-
 * 
 * Sets a constant ReposWeb with the root path for includes
 * 
 * @package admin
 */

define('ReposWeb', isset($_SERVER['REPOS_LOCAL_WEB']) ?
	$_SERVER['REPOS_LOCAL_WEB']
	: isset($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'].'/repos-web/') ?
	$_SERVER["DOCUMENT_ROOT"].'/repos-web/'
	: dirname(dirname(__FILE__)).'/repos-web/');

?>