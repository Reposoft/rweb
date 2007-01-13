<?php
/**
 * Represents the generation of a web page.
 * 
 * Presentation can not use the functions from /account, so user settings must be read from cookies.
 * Presentation is for all webapp pages, except administration reports.
 * 
 * PHP scripts should require this class or the Report class, depending on the type of output they produce.
 * 
 * @package conf
 * @see Report, the class used for unit tests and administration reports. Does not require repos.properties.php.
 */

// function called before any other output or headers
// TODO setHeader, setContentType, getContentType - same functions as in Report.class.php, define constants ...
function setupResponse() {
	repos_getUserLocale();
}

/**
 * All user presentation pages need repos.properties.php, but test pages should be able to mock it
 */
if (!function_exists('getRepository')) require(dirname(__FILE__).'/repos.properties.php');

if (isRequestService()) {
	require_once(dirname(dirname(__FILE__)).'/lib/json/json.php');
	header('Content-type: text/plain');
} else {
// don't know why the content type is not correct by default
//use when the namespace-based validator lib has been replaced by a classbased: header('Content-type: application/xhtml+xml; charset=UTF-8');
header('Content-type: text/html; charset=utf-8');
// don't set the content type headers in the HTML, because then we can't change to xhtml+xml later
}

// -------- user settings from cookies ---------

function repos_getUserTheme($user = '') {
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
function repos_getUserLocale() {
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
 * <code>..$foo,bar} {$foo,isBar..</code> becomes <code>..$foo->getBar()} {$foo->isBar()..<code>
 * Comma is used instead of dot so it can be used with the standard Smarty
 * syntax for associative arrays.
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

	// singleton accessor, for occations when it is not known if a Presentation object has already been allocated for the request
	// static, use Presentation::getInstance()
	function getInstance() {
		if (isset($GLOBALS['_presentationInstance'])) return $GLOBALS['_presentationInstance'];
		return new Presentation();
	}
	
	/**
	 * Constructor initializes a Smarty instance and adds custom filders.
	 */
	function Presentation() {
		setupResponse();
		
		$this->smarty = smarty_getInstance();
		
		// enforce singleton rule, but only after delimiters and such things has been configures (otherwise the error template might be invalid)
		if (isset($GLOBALS['_presentationInstance'])) {
			trigger_error("Code error. An attempt was made to create a second page instance", E_USER_ERROR);
		}
		$GLOBALS['_presentationInstance'] = $this;
		
		// register the prefilter
		$this->smarty->register_prefilter('Presentation_useCommentedDelimiters');
		$this->smarty->load_filter('pre', 'Presentation_useCommentedDelimiters');
		$this->smarty->register_prefilter('Presentation_useDotNotationForObjects');
		$this->smarty->load_filter('pre', 'Presentation_useDotNotationForObjects');
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
	 * executes & returns or displays the template results
	 *
	 * @param string $resource_name
	 * @param string $cache_id
	 * @param string $compile_id
	 * @param boolean $display
	 */
	function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
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
		// display
		if (!$resource_name) {
			$resource_name = $this->getDefaultTemplate();
		}
		if (//debug:// false && 
			$this->isRedirectBeforeDisplay()) {
			// TODO how to get PHP errors and warnings into the result page instead of before the redirect
			$file = tempnam(getTempDir('pages'),'');
			$pagecontents = $this->fetch($resource_name, $cache_id, $compile_id);
			$handle = fopen($file, "w");
			fwrite($handle, $pagecontents);
			fclose($handle);
			// should be handled by the root page, but on the same hostso getWebapp() can not be used
			$nexturl = getWebapp() . 'view/?result=' . basename($file);
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
	
	function _getThemeHeadTags() {
		$theme = repos_getUserTheme();
		$style = getWebappStatic().$theme.'style/';
		return $this->_getAllHeadTags($style);
	}
	
	/**
	 * @param stylePath the path to the current theme's 'style/' directory
	 */
	function _getAllHeadTags($stylePath) {
		$head = $this->_getMandatoryHeadTags($stylePath);
		foreach ($this->extraStylesheets as $css) {
			$head = $head . $this->_getLinkCssTag($stylePath.$css);
		}
		return $head;
	}
	
	/**
	 * @return tags to include in <head> at all pages
	 */
	function _getMandatoryHeadTags($stylePath) {
		return $this->_getLinkCssTag($stylePath.'global.css') .
			'<script type="text/javascript" src="'.getWebappStatic().'scripts/head.js"></script>';
	}
	
	function _getLinkCssTag($href) {
		return '<link href="'.$href.'" rel="stylesheet" type="text/css"></link>';
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
		return getWebapp().'account/login/';
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

function Presentation_useDotNotationForObjects($tpl_source, &$smarty)
{
	$patterns[0] = '/\$(\w+)\,is(\w+)/';
	$patterns[1] = '/\$(\w+)\,(\w+)/';
	$replacements[0] = '$$1->is$2()';
	$replacements[1] = '$$1->get$2()'; // would be good to uppercase first letter of $2
	return preg_replace($patterns,$replacements,$tpl_source);
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
