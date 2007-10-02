<?php
/**
 * Content streaming and templating (c) 2006-1007 Staffan Olsson www.repos.se
 * 
 * Presentation can not use the functions from /account, so user settings must be read from cookies.
 * Presentation is for all webapp pages, except administration reports.
 * 
 * PHP scripts should require this class or the Report class, depending on the type of output they produce.
 * 
 * Note that for production release,
 * Smarty caching should be ON (in smarty.inc.php)
 * and filters disabled.
 * 
 * @package conf
 * @see Report, the class used for unit tests and administration reports. Does not require repos.properties.php.
 */

// All user presentation pages need repos.properties.php, but test pages should be able to mock it.
if (!function_exists('getRepository')) require(dirname(__FILE__).'/repos.properties.php');
// The current redirect-after-post solution needs System for temp folder
if (!class_exists('System')) require(dirname(__FILE__).'/System.class.php');

/**
 * Use the configuration entry disable_caching to mark a general development mode
 */
define('PRESENTATION_DEBUG_MODE', getConfig('disable_caching') ? true : false); // enable/disable smarty caching
define('TEMPLATE_CACHING', !PRESENTATION_DEBUG_MODE);
// allow automatic validation during development, true->application/xhtml+xml, false->text/html
define('PRESENTATION_XTHML', PRESENTATION_DEBUG_MODE && strpos($_SERVER['HTTP_USER_AGENT'],'Gecko'));

// function called before any other output or headers
if (!function_exists('setupResponse')) {
function setupResponse() {
	// set cookie headers
	getUserLocale();
	// set the content type header, Presentation can generate json and XHTML
	if (headers_sent()) return;
	if (isRequestService()) {
		require_once(dirname(dirname(__FILE__)).'/lib/json/json.php');
		header('Content-Type: text/plain');
	} else {
		if (PRESENTATION_XTHML) {
			header('Content-Type: application/xhtml+xml; charset=utf-8');
		} else {
			header('Content-Type: text/html; charset=utf-8');
		}
	}
}
}

// -------- user settings from cookies ---------

function getUserTheme($user = '') {
	if (!isset($_COOKIE[THEME_KEY])) return '';
	$style = $_COOKIE[THEME_KEY];
	if ($style=='repos') return ''; // default stylesheet title is 'repos'
	return "themes/$style/";
}

$possibleLocales = array(
	'sv' => 'Svenska',
	'en' => 'English',
	'de' => 'Deutsch'
	);
	
/**
 * Resolve locale code from: 1: GET, 2: SESSION, 3: browser
 * @return two letter language code, lower case
 */
function getUserLocale() {
	global $possibleLocales;
	static $locale = null;
	if (!is_null($locale)) return $locale;
	$locale = 'en'; 
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $locale = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	if(array_key_exists(LOCALE_KEY,$_COOKIE)) $locale = $_COOKIE[LOCALE_KEY];
	if(array_key_exists(LOCALE_KEY,$_GET)) $locale = $_GET[LOCALE_KEY];
	// validate that the locale exists
	if( !isset($possibleLocales[$locale]) ) {
		$locale = array_shift(array_keys($possibleLocales));
	}
	// save and return
	if (!isset($_COOKIE[LOCALE_KEY])) {
		setcookie(LOCALE_KEY,$locale,0,'/');
	} else {
		$_COOKIE[LOCALE_KEY] = $locale;
	}
	return $locale;	
}

// ------- plugin functionality ----------
// all plugins should declare a function [name]_getHeadTags($webapp)
// that returns an array of HTML tags to be added to head, with $webapp as repos url

$_plugins = array();
function addPlugin($name) {
	global $plugins;
	if (in_array($name, $plugins)) return; // already loaded
	$inc = dirname(dirname(__FILE__))."/plugins/$name/$name.inc.php";
	if (!file_exists($inc)) trigger_error("The page tried to load a plugin '$name' that does not exist", E_USER_ERROR);
	require_once($inc);
	$plugins[] = $name;
}

// -------- the template class ------------
require(dirname(dirname(__FILE__)).'/lib/smarty/smarty.inc.php' );

/**
 * Extends smarty framework, to provide a customized presentation engine with well known syntax.
 *
 * This class only delegates to its internal Smarty instance,
 * so the inferface is limited to the functions mirrored from Smarty.
 * 
 * Does the following assigns for every page:
 * head = all shared head tags, place a <!--{head}--> in the <head> of the template
 * referer = the HTTP referer url, if there is one
 * userhome = a place where the current user always can go, if there is no other way out
 *
 * Allows two different markup delimiters:
 * <!--{ ... }-->
 * {= ... }
 * 
 * Also adds a prefilter that allows "dot" notation, but with comma, for objects
 * <code>..$foo,bar} {$foo,isBar..</code> becomes 
 * <code>..$foo->getBar()} {$foo->isBar()..</code>
 * Comma is used instead of dot so it can be combined with the standard Smarty
 * syntax for associative arrays.
 * 
 * All templates have the following variables, assigned in display():
 * 'head' - the common head tags.
 * 'referer' - the http referer, if there is one.
 * 'userhome' - the place which users can always return to if everything else goes wrong.
 * 'webapp' - the root URL to Repos, with trainling slash.
 * 
 * Cache settings are defined in te include file.
 */
class Presentation {

	/**
	 * The template we delegate to, with a subset of the functions.
	 * @var Smarty
	 */
	var $smarty;
	
	var $redirectBeforeDisplay = false;
	var $extraStylesheets = array();

	/**
	 * Singleton accessor, for occations when it is not known if a Presentation object has already been allocated for the request.
	 * Static, use Presentation::getInstance().
	 * @return Presentation
	 * @static
	 */ 
	function getInstance() {
		static $instance;
		if (!isset($instance)) {
			$c = __CLASS__;
			$instance = new $c;
			if (true && function_exists('displayEdit') && isset($_GET[SUBMIT])) {
				// automatic redirect-after-post
				$instance->enableRedirectWaiting();
			}
		}
		return $instance;
	}
	
	/**
	 * Constructor initializes a Smarty instance and adds custom filters.
	 * This function does everything that is required befor compiling templates.
	 * @private This class is Singleton. Use Presentation::getInstance().
	 */
	function Presentation() {
		setupResponse();
		
		$this->smarty = smarty_getInstance();
		
		$this->smarty->register_prefilter('Presentation_useCommentedDelimiters');
		$this->smarty->load_filter('pre', 'Presentation_useCommentedDelimiters');
		$this->smarty->register_prefilter('Presentation_urlRewriteForHttps');
		$this->smarty->load_filter('pre', 'Presentation_urlRewriteForHttps');
		$this->smarty->register_prefilter('Presentation_useDotNotationForObjects');
		$this->smarty->load_filter('pre', 'Presentation_useDotNotationForObjects');
		$this->smarty->register_prefilter('Presentation_removeIndentation');
		$this->smarty->load_filter('pre', 'Presentation_removeIndentation');
		$this->smarty->register_prefilter('Presentation_urlEncodeQueryString');
		$this->smarty->load_filter('pre', 'Presentation_urlEncodeQueryString');
		if (PRESENTATION_XTHML) {
			$this->smarty->register_prefilter('Presentation_useXmlEntities');
			$this->smarty->load_filter('pre', 'Presentation_useXmlEntities');
		}
		if (PRESENTATION_DEBUG_MODE) {
			$this->smarty->register_prefilter('Presentation_noExtraContentType');
			$this->smarty->load_filter('pre', 'Presentation_noExtraContentType');
		}
	}
	
	/**
	 * Customize smarty's trigger_error (for internal use, call showError instead)
	 */
	function trigger_error($error_msg) {
		$this->showError($error_msg);
		// if showError causes an internal trigger_error, we should end up here
		echo ("<!-- PHP error message: \n\n");
		$this->smarty->trigger_error($error_msg);
		echo ("\n-->\n");
		// we never want to continue after error
		exit;
	}
	
	/**
	 * assigns values to template variables
	 *
	 * @param array|string $tpl_var the template variable name(s)
	 * @param mixed $value the value to assign
	 */
	function assign($tpl_var, $value = null) {
		$this->smarty->assign($tpl_var, $value);	
	}
	
	/**
    * assigns values to template variables by reference
    *
    * @param string $tpl_var the template variable name
    * @param mixed $value the referenced value to assign
    */
	function assign_by_ref($tpl_var, &$value) {
		$this->smarty->assign_by_ref($tpl_var, $value);
	}
	
	/**
	 * appends values to template variables
	 *
	 * @param array|string $tpl_var the template variable name(s)
	 * @param mixed $value the value to append
	 */
	function append($tpl_var, $value=null, $merge=false) {
		$this->smarty->append($tpl_var, $value, $merge);	
	}
	
	/**
	 * appends values to template variables by reference
	 *
	 * @param string $tpl_var the template variable name
	 * @param mixed $value the referenced value to append
	 */
	function append_by_ref($tpl_var, &$value, $merge=false) {
	    $this->smarty->append_by_ref($tpl_var, $value, $merge);
	}
	
	/**
	 * executes & returns or displays the template results, 
	 * unlike display this does not assign any common variables.
	 *
	 * @param string $resource_name
	 * @param string $cache_id
	 * @param string $compile_id
	 * @param boolean $display
	 */
	function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
		// to get predictable cache path, always use forward slashes
		$resource_name = strtr($resource_name, '\\', '/');
		// no extra processing with fetch, just process the template with explicit assigns
		return $this->smarty->fetch($resource_name, $cache_id, $compile_id, $display);	
	}
	
	/**
	 * Smarty's default behaviour and some additions:
	 * + If enableRedirect() has been called, it does a redirect before display.
	 * + $resource_name is not mandatory,
	 * 	default template file name is [script minus .php]_[locale].html
	 */
	function display($resource_name = null, $cache_id = null, $compile_id = null) {
		if (isRequestService()) {
			return $this->displayInternal();
		}
		// set common head tags
		$this->assign('head', $this->_getThemeHeadTags());
		$this->assign('referer', $this->getReferer());
		$this->assign('userhome', $this->getUserhome());
		$this->assign('webapp', $this->_getStaticWebappUrl());
		if ($this->isUserLoggedIn()) {
			$this->assign('logout', '/?logout');
		}
		// display
		if (!$resource_name) {
			$resource_name = $this->getDefaultTemplate();
		}
		// to get predictable cache path, always use forward slashes
		$resource_name = strtr($resource_name, '\\', '/');
		// custom processing for redirect before display
		if ($this->isRedirectBeforeDisplay()) {
			if ($this->redirectBeforeDisplay===true) {
				if (headers_sent()) trigger_error('Failed to redirect to result page - output started already', E_USER_ERROR);
				$file = System::getTempFile('pages');
			} else {
				$file = System::getApplicationTemp('pages').$this->redirectBeforeDisplay;	
			}
			if (!$file) trigger_error('Failed to write response because server file could not be opened', E_USER_ERROR);
			$pagecontents = $this->fetch($resource_name, $cache_id, $compile_id);
			$handle = fopen($file, "w");
			fwrite($handle, $pagecontents);
			fclose($handle);
			$nexturl = $this->_getStaticWebappUrl() . 'view/?result=' . basename($file);
			header("Location: $nexturl");
		} else {
			$this->smarty->display($resource_name, $cache_id, $compile_id);
		}
	}
	
	/**
	 * Presents the assigned variables as a JSON string, and the entire page as text/plain (see the top of this script file).
	 * Note that it is standard JSON to escape all forward slashes with a backslash.
	 */
	function displayInternal() {
		$data = $this->smarty->get_template_vars();
		unset($data['SCRIPT_NAME']); // internal Smarty field
		$json = new Services_JSON(); // SERVICES_JSON_LOOSE_TYPE?
		echo $json->encode($data);
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
		$locale = getUserLocale();
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
	 * @param boolean true to enable redirect, false to disable
	 */	
	function enableRedirect($doRedirectBeforeDisplay=true) {
		$this->redirectBeforeDisplay = $doRedirectBeforeDisplay;
	}
	
	function isRedirectBeforeDisplay() {
		return $this->redirectBeforeDisplay != false;
	}
	
	function enableRedirectWaiting() {
		$pageid = uniqid();
		$this->enableRedirect($pageid);
		$nexturl = $this->_getStaticWebappUrl() . 'view/?result=' . rawurlencode($pageid) . '&w=0';
		ignore_user_abort(true);
		// quite the same response as in view/index.php wait
		$page = '<html><body><h4>Processing...</h4></body></html>';
		header('Refresh: 1; url='.$nexturl);
		header('Content-Length: '.strlen($page));
		echo $page;
		ob_flush();flush(); // according to the docs this is still not 100% reliable
	}
	
	/**
	 * Shows error page, by redirecting the browser to a page so that the URL is not tried again.
	 *
	 * @param String $error_msg the body of the error page, can contain HTML tags
	 * @param String $headline the contents of the <h1> tag
	 */
	function showError($error_msg, $headline='An error occurred') {
		// get template from this folder, not the importing script's folder
		$template = $this->getLocaleFile(dirname(__FILE__) . '/Error');
		$this->enableRedirect();
		$this->assign('headline', $headline);
		$this->assign('error', $error_msg);
		$this->display($template);
	}
	
	/**
	 * Shows error message without redirect, applicable when a browser refresh can't do any harm
	 * 
	 * @param String $error_msg the body of the error page, can contain HTML tags
	 * @param String $headline the contents of the <h1> tag
	 */
	function showErrorNoRedirect($error_msg, $headline='An error occurred') {
		$template = $this->getLocaleFile(dirname(__FILE__) . '/Error');
		$this->assign('headline', $headline);
		$this->assign('error', $error_msg);
		$this->display($template);		
	}
	
	/**
	 * @param the stylesheet path, given without starting '/' from the style/ path in any theme
	 */
	function addStylesheet($urlRelativeToTheme) {
		$this->extraStylesheets[] = $urlRelativeToTheme;
	}
	
	/**
	 * Get the base URL for images and css (not javascript)
	 */
	function _getStaticWebappUrl() {
		static $w = null;
		if (is_null($w)) $w = asLink(getWebapp());
		// even if SSL, use https for static resources too, to avoid browser warnings
		return $w;
	}
	
	/**
	 * Get the contents of the $head parameter in templates.
	 */
	function _getThemeHeadTags() {
		$theme = getUserTheme();
		return $this->_getAllHeadTags($this->_getStaticWebappUrl(), $theme.'style/');
	}
	
	/**
	 * @param stylePath the path to the current theme's 'style/' directory
	 */
	function _getAllHeadTags($webapp, $stylePath) {
		$head = $this->_getLinkCssTag($webapp.$stylePath.'global.css');
		foreach ($this->extraStylesheets as $css) {
			$head = $head . $this->_getLinkCssTag($webapp.$stylePath.$css);
		}
		$head = $head . $this->_getPluginHeadTags($webapp);
		// allow the pages to avoid javascripts if no plugins are loaded
		if (strContains($head, '<script ')) {
			$head = '<script type="text/javascript" src="'.$webapp.'scripts/head.js"></script>'.$head;
		}
		return $head;
	}
	
	function _getLinkCssTag($href) {
		return '<link href="'.$href.'" rel="stylesheet" type="text/css"></link>';
	}
	
	function _getPluginHeadTags($webapp) {
		global $plugins;
		$h = array();
		for ($i=0; $i<count($plugins); $i++) {
			$ph = call_user_func($plugins[$i].'_getHeadTags', $webapp);
			$h = array_merge($ph, $h);
		}
		return implode("\n", $h);
	}
	
	/**
	 * @return boolean true if the browser viewing the results has a logged in user
	 */
	function isUserLoggedIn() {
		return function_exists('isLoggedIn') && isLoggedIn();
	}
	
	/**
	 * @return the current page's 'back' url, using
	 * 1: $_REQUEST['referer'], 2: getHttpReferer() (only if it's not a result=tmp page)
	 * if no good referer found, use javascript.
	 */
	function getReferer() {
		// allow referer to be set explicitly, for example to 
		//  have the same home button thoughout a wizard
		if (isset($_REQUEST['referer'])) {
			return $_REQUEST['referer'];
		}
		// use javascript in redirect-after-post results
		if (isset($_GET['result'])) {
			return "javascript:history.go(-1)";	
		}
		// use javascript after form, to preserve values
		if (isset($_REQUEST[SUBMIT])) {
			return "javascript:history.go(-1)";
		}
		// get from requiest headers TODO use getHttpReferer
		if (getHttpReferer() && !isset($_GET['result'])) {
			return getHttpReferer();
		}
		// if nothing else can be found
		return "javascript:history.go(-1)";
	}
	
	/**
	 * @return repos home page on this server
	 */
	function getUserhome() {
		if ($this->isUserLoggedIn()) {
			return getWebapp().'account/login/';
		} else {
			return '/'; // server root is startpage for everyone else
		}
	}
}

function Presentation_noExtraContentType($tpl_source, &$smarty)
{
	if (strpos($tpl_source, 'http-equiv="Content-Type"')) {
		return 'Application error. The template for this page contains Content-Type tag.';	
	}
	return $tpl_source;
}

function Presentation_useCommentedDelimiters($tpl_source, &$smarty)
{
	$patterns[0] = '/<!--{/';
	$patterns[1] = '/}-->/';
	$replacements[0] = LEFT_DELIMITER;
	$replacements[1] = RIGHT_DELIMITER;
   return preg_replace($patterns,$replacements,$tpl_source);
}

function Presentation_useXmlEntities($tpl_source, &$smarty)
{
	$patterns[0] = '/&(?!\w{2,6};)/';
	$replacements[0] = '&amp;';
   return preg_replace($patterns,$replacements,$tpl_source);
}

function Presentation_useDotNotationForObjects($tpl_source, &$smarty)
{
	$patterns[0] = '/\$(\w+)\,is(\w+)/';
	$patterns[1] = '/\$(\w+)\,(\w+)/';
	$replacements[0] = '$$1->is$2()';
	$replacements[1] = '$$1->get$2()'; // would be good to uppercase first letter of $2
	return preg_replace($patterns,$replacements,$tpl_source);
}

function Presentation_urlRewriteForHttps($tpl_source, &$smarty) {
	$pattern = '/(href|src)=\"\{=\$([\w,.]+)\}/';
	$replacement = '$1="{=$$2|asLink}';
	return preg_replace($pattern, $replacement, $tpl_source);
}

/**
 * Rawurlencode every 'target' query parameter value (if there is no passthru function for it already)
 */
function Presentation_urlEncodeQueryString($tpl_source, &$smarty) {
	$pattern = '/(href)=\"([^"]*\?[^"]*target=)\{=\$([\w,.]+)\}/';
	$replacement = '$1="$2{=$$3|rawurlencode}';
	return preg_replace($pattern, $replacement, $tpl_source);
}

function Presentation_removeIndentation($tpl_source, &$smarty) {
	$pattern = '/>\r?\n\s*([<{])/';
	$replacement = '>$1';
	return preg_replace($pattern, $replacement, $tpl_source); 
}

// plug in to repos.properties.php's error handling solution
if (!function_exists('reportErrorToUser')) { function reportErrorToUser($n, $message, $trace) {
	if ($n==E_USER_ERROR || $n==E_USER_WARNING || $n==E_USER_NOTICE) {
		$p = Presentation::getInstance();
		if (headers_sent()) {
			echo("<strong>Error:</strong> ".nl2br($message)."<!-- Error level $n. Stack trace:\n$trace -->"); exit;
		} else {
			$p->showErrorNoRedirect("Error: ".nl2br($message)."<!-- Error level $n. Stack trace:\n$trace -->");
		}
		exit(1);
	}
}}

?>
