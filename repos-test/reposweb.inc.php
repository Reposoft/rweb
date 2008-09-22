<?php
/**
 * Defines the dependency to Repos Web-
 * 
 * Sets a constant ReposWeb with the webapp path.
 * Sets a constant ReposWebapp with the webapp url.
 * 
 * @package admin
 */

if (!defined('ReposWeb')) define('ReposWeb', $_SERVER["DOCUMENT_ROOT"].'/repos-web/');
if (!defined('ReposWebapp')) define('ReposWebapp', '/repos-web/');

?>
