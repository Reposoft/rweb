<?php
if (!class_exists('System')) require(dirname(dirname(dirname(__FILE__))).'/conf/System.class.php');

if (!file_exists(dirname(__FILE__).'/libs/')) {
	trigger_error("Smarty 'libs' folder has not been installed. Go to repos/lib/ to install it.");
}
require(dirname(__FILE__).'/libs/Smarty.class.php');

// globale template settings, compatible with inline with css and javascript
define('LEFT_DELIMITER', '{=');
define('RIGHT_DELIMITER', '}');

// the four cache subdirectories must be writable by webserver
define('CACHE_DIR', dirname(__FILE__).'/cache/');

// in production cache may be precompiled, which is then flagged using a file
if (file_exists(CACHE_DIR.'/COMPLETE')) define('TEMPLATE_PRODUCTION', true);

// during development you may want to disable all kinds of caching (overrides PRODUCTION)
//define('TEMPLATE_CACHING', false);

// smarty 2.6.14 sends error message if SMARTY_DEBUG is not set
if (!isset($_COOKIE['SMARTY_DEBUG'])) $_COOKIE['SMARTY_DEBUG'] = 0;

// use paths relative to webapp root for smarty template caching, always use forward slashes
define('TEMPLATE_BASE', strtr(dirname(dirname(dirname(__FILE__))), '\\', '/'));

/**
 * Create and configure a template engine instance
 *
 * @return Smarty instance with no filters, ready for assigns
 */
function smarty_getInstance() {
	$s = new Smarty();

	if (defined('TEMPLATE_CACHING') && TEMPLATE_CACHING===false) {
		$s->caching = false;
		$s->force_compile = true;
		$s->debugging_ctrl = 'URL';
	} else if (defined('TEMPLATE_PRODUCTION') && TEMPLATE_PRODUCTION) {
		$s->compile_check = false;
		$s->caching = false; // never cache the result of templates	
	} else {
		$s->caching = false;	
	}
	
	$s->template_dir = CACHE_DIR.'templates/';
	$s->compile_dir = CACHE_DIR.'templates_c/';
	$s->config_dir = CACHE_DIR.'configs/';
	$s->cache_dir = CACHE_DIR.'cache/';

	$s->left_delimiter = LEFT_DELIMITER;
	$s->right_delimiter = RIGHT_DELIMITER;

	return $s;
}

?>