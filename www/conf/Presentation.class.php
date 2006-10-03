<?php
/**
 * Represents the generation of a web page.
 * Extends smarty framework, so use the same syntax as any smarty teplate
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
 * Cache settings are defined in te include file.
 */

// this page is included from other pages: need to do includes relative to __FILE__
require_once(dirname(__FILE__).'/repos.properties.php');

// don't know why the content type is not correct by default
//use when the namespace-based validator lib has been replaced by a classbased: header('Content-type: application/xhtml+xml; charset=UTF-8');
header('Content-type: text/html; charset=utf-8');
// don't set the content type headers in the HTML, because then we can't change to xhtml+xml later

// --- selfchecks ---

// ------ form validation support -------
require_once(dirname(dirname(__FILE__)).'/plugins/validation/validation.inc.php');

// ---- standard rules that the pages can instantiate ----

/**
 * Shared validation rule representing file- or foldername.
 * 
 * Not required field. Use Validation::expect(...) to require.
 * 
 * Basically same rules as in windows, but max 50 characters, 
 * no \/:*?"<> or |.
 */
class FilenameRule extends RuleEreg {
	var $required;
	function FilenameRule($fieldname, $required='true') {
		$this->required = $required;
		$this->RuleEreg($fieldname, 
			'may not contain any of the characters \/:*?<>| or quotes', 
			'^[a-zA-Z0-9.]+$');
	}
	function validate($value) {
		if (empty($value)) return $this->required ? 'required' : null;
		if (strlen($value) > 50) return "max length 50";
		return parent::validate($value);
	}
}

// -------- user settings ---------

function repos_getUserTheme($user = '') {
	if(!function_exists('getReposUser()')) {
		return ''; // account logic not imported, this is a public page that uses the default theme
	}
	if ($user=='') {
		$user = getReposUser();
	}
	if (empty($user)) return '';
	if ($user=='test'||$user=='annika'||$user=='arvid'||$user=='hanna') return '';
	return 'themes/pe/';
}

// -------- the template class ------------
require(dirname(dirname(__FILE__)).'/lib/smarty/smarty.inc.php' );

class Presentation extends Smarty {

	var $redirectBeforeDisplay = false;
	var $extraStylesheets = array();

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
			$handle = fopen($file, "w");
			fwrite($handle, $this->fetch($resource_name, $cache_id, $compile_id));
			fclose($handle);
			// should be handled by the root page
			$nexturl = getWebapp() . 'view/?result=' . basename($file);
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
	 * Shows error message without redirect, applicable when a reload can't do any harm
	 * @param unknown_type $error_msg
	 */
	function showErrorNoRedirect($error_msg) {
		$template = $this->getLocaleFile(dirname(__FILE__) . '/Error');
		$this->assign('error_msg', $error_msg);
		$this->display($template);		
	}
	
	/**
	 * "$this->display" but resolves template name automatically
	 * @deprecated display with no parameter works the same way
	 */
	function show() {
		trigger_error("show() should be replaced with display()");
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
		$style = getWebapp().$theme.'style/';
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
			'<script type="text/javascript" src="'.getWebapp().'scripts/head.js"></script>';
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
		if (isset($_REQUEST['submit'])) {
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

if (!function_exists('reportErrorToUser')) { function reportErrorToUser($n, $message, $trace) {
	if ($n==E_USER_ERROR || $n==E_USER_WARNING || $n==E_USER_NOTICE) {
		$p = new Presentation();
		$p->showErrorNoRedirect(nl2br($message)."<!-- Error level $n. Stack trace:\n$trace -->");
		exit(1);
	}
}}

?>
