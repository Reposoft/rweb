<?php
if (!class_exists('System')) require(dirname(dirname(dirname(__FILE__))).'/conf/System.class.php');

// this file is not needed anymore. simply instantiate Presentation.
if (!file_exists(dirname(__FILE__).'/libs/')) {
	trigger_error("Smarty 'libs' folder has not been installed. Go to repos/lib/ to install it.");
}
require(dirname(__FILE__).'/libs/Smarty.class.php');

define('LEFT_DELIMITER', '{='); // to be able to mix with css and javascript
define('RIGHT_DELIMITER', '}');

// make it possible to disable cache during development
define('CACHING', false);

// the four cache subdirectories must be writable by webserver
define('CACHE_DIR', System::getApplicationTemp('smarty-cache'));
if ( ! file_exists(CACHE_DIR.'templates/') ) {
	mkdir( CACHE_DIR.'templates/' );
	mkdir( CACHE_DIR.'templates_c/' );
	mkdir( CACHE_DIR.'configs/' );
	mkdir( CACHE_DIR.'cache/' );
}

// smarty 2.6.14 sends error message if SMARTY_DEBUG is not set
if (!isset($_COOKIE['SMARTY_DEBUG'])) $_COOKIE['SMARTY_DEBUG'] = 0;

?>