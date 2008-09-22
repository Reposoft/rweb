<?php
/**
 * Defines the dependency to Repos Web-
 * 
 * Sets a constant ReposWeb with the webapp path.
 * Sets a constant ReposWebapp with the webapp url.
 * 
 * @package admin
 */

if (!defined('ReposWeb')) define('ReposWeb',
		isset($_SERVER['REPOS_LOCAL_WEB']) ?
		$_SERVER['REPOS_LOCAL_WEB']
		: dirname(dirname(__FILE__)).'/repos-web/');
if (!defined('ReposWebapp')) define('ReposWebapp', '/repos-web/');

?>
