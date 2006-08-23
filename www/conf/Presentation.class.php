<?php
require_once(dirname(__FILE__).'/language.inc.php');

// configure Smarty (http://smarty.php.net/) as template engine
define("SMARTY_DIR",dirname(dirname(__FILE__)).'/smarty/libs/');
require( SMARTY_DIR.'Smarty.class.php' );

define('LEFT_DELIMITER', '{='); // to be able to mix with css and javascript
define('RIGHT_DELIMITER', '}');
// there is also a filter so that delimiters <!--{ and }--> are supported.

// make it possible to disable cache during development
define('CACHING', false);

// the four cache subdirectories must be writable by webserver
define('CACHE_DIR', getTempDir('smarty-cache'));
if ( ! file_exists(CACHE_DIR.'templates/') ) {
	mkdir( CACHE_DIR.'templates/' );
	mkdir( CACHE_DIR.'templates_c/' );
	mkdir( CACHE_DIR.'configs/' );
	mkdir( CACHE_DIR.'cache/' );
}

// smarty factory
class Presentation extends Smarty {

	// constructor
	function Presentation() {
		$this->caching = CACHING;
		if (!CACHING) {
			$this->force_compile = true;
			// allow SMARTY_DEBUG query string parameter
			$this->debugging_ctrl = 'URL';
		}
		
		$this->template_dir = CACHE_DIR.'templates/';
		$this->compile_dir = CACHE_DIR.'templates_c/';
		$this->config_dir = CACHE_DIR.'configs/';
		$this->cache_dir = CACHE_DIR.'cache/';
	
		$this->left_delimiter = LEFT_DELIMITER;
		$this->right_delimiter = RIGHT_DELIMITER;
		
		// register the prefilter
		$this->register_prefilter('Presentation_useCommentedDelimiters');
		$this->load_filter('pre', 'Presentation_useCommentedDelimiters');	
		
		// set common head tags
		$this->assign('head', $this->getCommonHeadTags());
		$this->assign('referer', $this->getReferer());
	}
	
	/**
	 * @return tags to include in <head> at all pages
	 */
	function getCommonHeadTags() {
		return '<link href="../themes/simple/css/repos-standard.css" rel="stylesheet" type="text/css" />';
	}
	
	/**
	 * @return the current request's referer
	 */
	function getReferer() {
		if (isset($_SERVER['HTTP_REFERER'])) {
			return $_SERVER['HTTP_REFERER'];
		}
		return "javascript:history.go(-1)";
	}
}

function Presentation_useCommentedDelimiters($tpl_source, &$smarty)
{
	$patterns[0] = '/<!--{/';
	$patterns[1] = '/}-->/';
	$replacements[0] = LEFT_DELIMITER;
	$replacements[1] = RIGHT_DELIMITER;
    return preg_replace($patterns,$replacements,$tpl_source);
}
?>
