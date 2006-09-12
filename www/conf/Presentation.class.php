<?php
require_once(dirname(__FILE__).'/repos.properties.php');

// don't know why the content type is not correct by default
//use when the namespace-based validator lib has been replaced by a classbased: header('Content-type: application/xhtml+xml; charset=UTF-8');
header('Content-type: text/html; charset=utf-8');
// don't set the content type headers in the HTML, because then we can't change to xhtml+xml later

// configure Smarty (http://smarty.php.net/) as template engine
define("SMARTY_DIR",dirname(dirname(__FILE__)).'/lib/smarty/libs/');
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

	var $redirectBeforeDisplay = false;

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
		$this->assign('userhome', $this->getUserhome());
	}
	
	/**
	 * Customize smarty's trigger_error
	 */
	function trigger_error($error_msg) {
		$this->showError($error_msg);
		// if showError causes an internal trigger_error, we should end up here
		echo ("<!-- PHP error message: \n\n");
		parent::trigger_error($error_msg);
		echo ("\n-->\n");
		// we never want to continue after error
		exit;
	}
	
	/**
	 * Smarty's default behaviour and some additions:
	 * + If enableRedirect() has been called, it does a redirect before display.
	 * + $resource_name is not mandatory,
	 * 	default template file name is [script minus .php]_[locale].html
	 */
	function display($resource_name = null, $cache_id = null, $compile_id = null) {
		if (!$resource_name) {
			$resource_name = $this->getDefaultTemplate();
		}
		if (//debug:// false && 
		  $this->isRedirectBeforeDisplay()) {
			// TODO how to get PHP errors and warnings into the result page instead of before the redirect
			$file = tempnam(getTempDir('pages'),'');
			$handle = fopen($file, "w");
			fwrite($handle, $this->fetch($resource_name, $cache_id, $compile_id));
			fclose($handle);
			// should be handled by the root page
			$nexturl = repos_getWebappRoot() . '/view/?result=' . basename($file);
			header("Location: $nexturl");
		} else {
			parent::display($resource_name, $cache_id, $compile_id);
		}
	}
	
	function getDefaultTemplate() {
		return $this->getLocaleFile(dirname($_SERVER['SCRIPT_FILENAME']).'/'.basename($_SERVER['SCRIPT_FILENAME'],".php"));
	}
	
	/**
	 * The one-stop-shop method to get the localized version of your template.
	 * @return filename of the localized version for this page
	 */
	function getLocaleFile($name,$extension='.html') {
		global $possibleLocales;
		$locale = repos_getUserLocale();
		$chosen = $this->getContentsFileInternal($locale,$name,$extension);
		if (file_exists($chosen)) return $chosen;
		foreach ($possibleLocales as $lo => $n) {
			$chosen = $this->getContentsFileInternal($lo,$name,$extension);
			if (file_exists($chosen)) return $chosen;
		}
		return "file_not_found $name $locale $extension";
	}
	
	/**
	 * Behavior can be overridden by iplementing getContentsFile($localeCode, $name, $extension)
	 * @return the filename representing a specified locale
	 */
	function getContentsFileInternal($localeCode, $name, $extension) {
		if (function_exists('getContentsFile')) return getContentsFile($localeCode, $extension);
		return $name . '_' . $localeCode . $extension;
	}

	/**
	 * Use redirect before page is displayed. Useful as redirect-after-post.
	 */	
	function enableRedirect($doRedirectBeforeDisplay=true) {
		$this->redirectBeforeDisplay = $doRedirectBeforeDisplay;
	}
	
	function isRedirectBeforeDisplay() {
		return $this->redirectBeforeDisplay;
	}
	
	function showError($error_msg) {
		// get template from this folder, not the importing script's folder
		$template = $this->getLocaleFile(dirname(__FILE__) . '/Error');
		$this->enableRedirect();
		$this->assign('error_msg', $error_msg);
		$this->display($template);
	}
	
	/**
	 * "$this->display" but resolves template name automatically
	 * 
	 */
	function show() {
		
		$this->display($template);
	}
	
	/**
	 * @return tags to include in <head> at all pages
	 */
	function getCommonHeadTags() {
		$theme = repos_getUserTheme();
		return 
			'<link href="'.getConfig('repos_web').'/'.$theme.'style/global.css" rel="stylesheet" type="text/css"></link>' .
			'<script type="text/javascript" src="'.getConfig('repos_web').'/scripts/head.js"></script>';
	}
	
	/**
	 * @return the current request's referer
	 */
	function getReferer() {
		// allow referer to be set explicitly, for example to 
		//  have the same home button thoughout a wizard
		if (isset($_REQUEST['referer'])) {
			return $_REQUEST['referer'];
		}
		// get from requiest headers
		if (isset($_SERVER['HTTP_REFERER'])) {
			return $_SERVER['HTTP_REFERER'];
		}
		// if nothing else can be found
		return "javascript:history.go(-1)";
	}
	
	/**
	 * @return repos home page on this server
	 */
	function getUserhome() {
		return getConfig('repos_web').'/account/login/';
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
