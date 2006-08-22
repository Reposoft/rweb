<?php
// configure Smarty (http://smarty.php.net/) as template engine
define("SMARTY_DIR",dirname(__FILE__).'/libs/');
require( SMARTY_DIR.'Smarty.class.php' );

define('LEFT_DELIMITER', '{='); // to be able to mix with css and javascript
define('RIGHT_DELIMITER', '}');
// there is also a filter so that delimiters <!--{ and }--> are supported.

define('CACHING', false); // to use during development

// smarty factory
function getTemplateEngine() {
	$template = new Smarty;
	
	$template->caching = CACHING;
	if (!CACHING) {
		// during development
		$template->force_compile = true;
	}
	
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


	$template->left_delimiter = LEFT_DELIMITER;
	$template->right_delimiter = RIGHT_DELIMITER;
	
	// register the prefilter
	$template->register_prefilter('useCommentedDelimiters');
	$template->load_filter('pre', 'useCommentedDelimiters');
	
	// allow SMARTY_DEBUG query string parameter
	$template->debugging_ctrl = 'URL';	
	
	$template->assign('head', getHead());
	
	return $template;
}

function useCommentedDelimiters($tpl_source, &$smarty)
{
	$patterns[0] = '/<!--{/';
	$patterns[1] = '/}-->/';
	$replacements[0] = LEFT_DELIMITER;
	$replacements[1] = RIGHT_DELIMITER;
    return preg_replace($patterns,$replacements,$tpl_source);
}

/**
 * @return tags to include in <head> at all pages
 */
function getHead() {
	echo ('<link href="../themes/simple/css/repos-standard.css" rel="stylesheet" type="text/css" />');
}
?>