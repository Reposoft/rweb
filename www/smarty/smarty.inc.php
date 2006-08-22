<?php
// configure Smarty (http://smarty.php.net/) as template engine
define("SMARTY_DIR",dirname(__FILE__).'/libs/');
require( SMARTY_DIR.'Smarty.class.php' );

// smarty factory
function getTemplateEngine() {
	$template = new Smarty;
	
	// the four cache subdirectories must be writable by webserver
	define("CACHE_DIR",dirname(__FILE__)."/cache/");
	if ( ! file_exists(CACHE_DIR) ) {
		if ( ! mkdir(CACHE_DIR) ) {
			echo "Error: could not create cache dir ".CACHE_DIR;
			exit;
		}
		mkdir( CACHE_DIR.'templates/' );
		mkdir( CACHE_DIR.'templates_c/' );
		mkdir( CACHE_DIR.'configs/' );
		mkdir( CACHE_DIR.'cache/' );
	}
	$template->template_dir = CACHE_DIR.'templates/';
	$template->compile_dir = CACHE_DIR.'templates_c/';
	$template->config_dir = CACHE_DIR.'configs/';
	$template->cache_dir = CACHE_DIR.'cache/';

	$template->left_delimiter = '{='; // to be able to mix with css and javascript
	$template->right_delimiter = '}';
	
	// allow SMARTY_DEBUG query string parameter
	$template->debugging_ctrl = 'URL';	
	
	return $template;
}
?>