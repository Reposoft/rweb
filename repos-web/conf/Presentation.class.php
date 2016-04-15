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
define('PRESENTATION_DEBUG_MODE', isset($_SERVER['ReposTestDisableCaching']) ? true : false); // enable/disable smarty caching
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

$possibleLocales = array(
	'en' => 'English',
	'sv' => 'Svenska',
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
	// validate that the locale exists, if not select default
	if( !isset($possibleLocales[$locale]) ) {
		$locale = array_keys($possibleLocales)[0];
	}
	return $locale;
}

/**
 * Same function as repos.xsl:getFileId and fileid.js
 * Replaces filename characters not valid in xhtml ids with _.
 * Note that a prefix of some kind is needed because ids must start with letter.
 *
 * @param String $name the file name, UTF-8 encoding, not urlencoded
 */
function getFileId($name) {
	// subversion and javascript fileid uses lowercase escape codes
	$e = preg_replace_callback('/[^A-Za-z:]+/','getFileId_lower',$name);
	return preg_replace('/[%\/\(\)@&]/','_',$e);
}

function getFileId_lower($matches) {
	return strtolower(rawurlencode($matches[0]));
}

// ------- plugin functionality ----------
// all plugins should declare a function [name]_getHeadTags($webapp)
// that returns an array of HTML tags to be added to head, with $webapp as repos url

function addPlugin($name) {
	// deprecated
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
	 *
	 * @return Presentation
	 * @static
	 */
	function getInstance() {
	//not php4://static function getInstance() {
		static $instance = null;
		if ($instance == null) {
			$c = __CLASS__;
			$instance = new $c;
		}
		return $instance;
	}

	/**
	 * For edit operations that want redirect-after-post, that must be enabled
	 *  after input validation (to simplify for validation frameworks)
	 *  but before operation starts (as soon as possible).
	 *
	 * They should call this method instead of getInstance.
	 * 
	 * Exceptions are made automatically to allow operations from clients other
	 * than browsers to get the result without redirect.
	 *
	 * To force redirect call Presentation->enableRedirectWaiting() on a newInstance().
	 *
	 * @return Presentation with redirect-after-post if the request
	 *  looks like being from a browser showing a form
	 * @static
	 */
	function background() {
		$p = Presentation::getInstance();
		if (isRequestService()) return $p;
		$p->enableRedirectWaiting();
		return $p;
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
		$this->smarty->register_prefilter('Presentation_urlEncodeQueryString');
		$this->smarty->load_filter('pre', 'Presentation_urlEncodeQueryString');
		$this->smarty->register_prefilter('Presentation_supportRepoBase');
		$this->smarty->load_filter('pre', 'Presentation_supportRepoBase');
		$this->smarty->register_prefilter('Presentation_useDotNotationForObjects');
		$this->smarty->load_filter('pre', 'Presentation_useDotNotationForObjects');
		//$this->smarty->register_prefilter('Presentation_removeIndentation');
		//$this->smarty->load_filter('pre', 'Presentation_removeIndentation');
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
		} elseif (isset($_REQUEST[WEBSERVICE_KEY])) {
			$this->smarty->register_outputfilter('Repos_cust_output_'.$_REQUEST[WEBSERVICE_KEY]);
		}
		// set common head tags
		$webapp = $this->_getStaticWebappUrl();
		$this->assign('referer', $this->getReferer());
		$this->assign('webapp', $webapp);
		// service URL mode
		$this->assign('isrealurl', isRealUrl());
		// repository root URL
		$this->assign('repository', getRepository());
		// support mod_dav_svn's @base attrubute for multirepo
		$this->assign('base', isset($_REQUEST['base']) ? $_REQUEST['base'] : '');
		// the dynamic part of the html header
		$this->assign('head', $this->_getHeadTags($webapp));
		// display
		if (!$resource_name) {
			$resource_name = $this->getDefaultTemplate();
		}
		// to get predictable cache path, always use forward slashes
		$resource_name = strtr($resource_name, '\\', '/');
		// custom processing for redirect before display
		if ($this->isRedirectBeforeDisplay()) {
			$file = $this->redirectBeforeDisplay; // absolute path if enableRedirectWithOutputFile was used
			if ($file===true) {
				if (headers_sent()) trigger_error('Failed to redirect to result page - output started already', E_USER_ERROR);
				$file = System::getTempFile('pages');
			}
			if (!$file) trigger_error('Failed to write response because server file could not be opened', E_USER_ERROR);
			$pagecontents = $this->fetch($resource_name, $cache_id, $compile_id);
			$handle = fopen($file, "w");
			fwrite($handle, $pagecontents);
			fclose($handle);
			$nexturl = $this->_getStaticWebappUrl() . 'view/?result=' . basename($file);
			// with enableRedirectWaiting the browser may have been redirected already and we just want to print the page
			if (!headers_sent()) header("Location: $nexturl");
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
		echo "\n"; // convenient for command line requests
	}

	function getDefaultTemplate() {
		$script = realpath($_SERVER['SCRIPT_FILENAME']); // we have to resolve symlinks becase __FILE__ in smarty.inc.php does so
		return $this->getLocaleFile(dirname($script).'/'.basename($script,".php"));
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
	
	function enableRedirectWithOutputFile($absolutePath) {
		$this->redirectBeforeDisplay = $absolutePath;
	}

	function isRedirectBeforeDisplay() {
		return $this->redirectBeforeDisplay != false;
	}

	function enableRedirectWaiting() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			trigger_error('Background processing is only allowed for POST requests', E_USER_ERROR);
		}
		if (isRequestService()) {
			trigger_error('Background processing not allowed for service requests', E_USER_ERROR);
		}
		$pageid = uniqid();
		$file = System::getApplicationTemp('pages').$pageid;
		header("X-REPOS-PAGEID: $pageid");
		// create the file so the view page knows the process is running
		if (!touch($file)) {
			trigger_error('Could not create buffer for POST message', E_USER_WARNING);
			return; // disable redirect
		}
		// store path		
		$this->enableRedirectWithOutputFile($file);
		// the view url
		$nexturl = $this->_getStaticWebappUrl() . 'view/?result=' . rawurlencode($pageid);
		// let this page continue
		ignore_user_abort(true);
		// redirect-after-post to the waiting page
		header('Location: '.$nexturl);
		echo 'Should have been redirected to result page.'; // browser will redirect before this is displayed
		@ob_flush();flush(); // make sure headers have been sent
		//sleep(2);//test
		// note that this page continues execution in the background
		// FIXME is there some way now to display fatal PHP errors?
	}

	/**
	 * Shows error page, by redirecting the browser to a page so that the URL is not tried again.
	 *
	 * @param String $error_msg the body of the error page, can contain HTML tags
	 * @param String $headline the contents of the <h1> tag
	 */
	function showError($error_msg, $headline='An error occurred') {
		if (!$this->isRedirectBeforeDisplay()) {
			$this->enableRedirect();
		}
		$this->showErrorNoRedirect($error_msg, $headline);
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
	 * @param the stylesheet path, relative to "style/"
	 */
	function addStylesheet($url) {
		$this->extraStylesheets[] = $url;
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
	 * @param stylePath the folder containing global.css, relative to webapp
	 */
	function _getHeadTags($webapp, $stylePath='style/') {
		$head = '';
		// metadata
		$head = $head . '<meta name="repos-repository" content="'.$this->smarty->get_template_vars('repository').'" />';
		if (function_exists('isTargetSet') && isTargetSet()) {
			$head = $head . '<meta name="repos-target" content="'.getTarget().'" />';
		}
		if (function_exists('getService')) {
			$head = $head . '<meta name="repos-service" content="'.getService().'" />';
		}
		if (isset($_REQUEST[WEBSERVICE_KEY])) {
			$head = $head . '<meta name="repos-serv" content="'.$_REQUEST[WEBSERVICE_KEY].'" />';
		}
		// metadata for multi-repo
		// unlike svn's index xml, Repos should set repos-base only if there is an SVNParentPath
		// (or, if the xslt always writes repos-base, we should do so here too)
		$base = $this->smarty->get_template_vars('base');
		if ($base) {
			$head = $head . '<meta name="repos-base" content="'.$base.'" />';
		}		
		// shared css and scripts
		$head = $head . $this->_getLinkCssTag($webapp.$stylePath.'global.css');
		foreach ($this->extraStylesheets as $css) {
			$head = $head . $this->_getLinkCssTag($webapp.$stylePath.$css);
		}
		$head = $head . '<script type="text/javascript" src="'.$webapp.'scripts/head.js"></script>';
		return $head;
	}

	function _getLinkCssTag($href) {
		return '<link href="'.$href.'" rel="stylesheet" type="text/css"></link>';
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
 * Note that object properties should be accessed using "object,property" notation instead of ->
 * Complex expressions should be placed in an 'assign' first before used in urls.
 */
function Presentation_urlEncodeQueryString($tpl_source, &$smarty) {
	$pattern = '/(href)=\"([^"]*\?[^"]*target=)\{=\$([\w,.]+)\}/';
	$replacement = '$1="$2{=$$3|rawurlencode}';
	return preg_replace($pattern, $replacement, $tpl_source);
}


/**
 * The @base attribute from mod_dav_svn would be easy to handle in a stateful application,
 * but in repos REST style pages we have to add it to every resource URL.
 * We do it here instead of in each page.
 * This filter in combination with the extra assign in Presentation adds @base support to all pages.
 */
function Presentation_supportRepoBase($tpl_source, &$smarty) {
	$patterns[0] = '/(href)=\"([^"]*)\?([^"]*target=)/';
	$replacements[0] = '$1="$2?base={=$base}&$3';
	$patterns[1] = '/(<input [^>]*name="target")/';
	$replacements[1] = '<input type="hidden" name="base" value="{=$base}"/>$1';
	return preg_replace($patterns, $replacements, $tpl_source);
}

function Presentation_removeIndentation($tpl_source, &$smarty) {
	$pattern = '/>\r?\n\s*([<{])/';
	$replacement = '>$1';
	return preg_replace($pattern, $replacement, $tpl_source);
}

/**
 * Modify html for embedded viewing, like an iframe.
 * Will of course not affect links generated in javascript.
 */
function Repos_cust_output_embed($tpl_output, &$smarty) {
	// TODO to make any difference this filter would have to really modify the HTML and get rid of command bar etc
	$patterns[] = '/<body>/';
	$replacements[] = '<body class="serv-embed">';
	$patterns[] = '/(<a [^>]*)href=/';
	$replacements[] = '$1target="_top" href=';
	return preg_replace($patterns, $replacements, $tpl_output);
}

// plug in to repos.properties.php's error handling solution
if (!function_exists('reportErrorToUser')) { function reportErrorToUser($n, $message, $trace) {
	$label = "Runtime error (code $n):";
	if ($n==E_USER_ERROR) $label = "Server error:";
	if ($n==E_USER_WARNING) $label = "Validation error:";
	if ($n==E_USER_NOTICE) $label = "Notice:";
	// need to supporess errors for this until we use "static" because PHP would print the E_STRICT warning
	$p = @Presentation::getInstance();
	// display user friendly error page
	if ($p->isRedirectBeforeDisplay()) {
		// Show error in redirect page. Result page currently has no support for sending status header.
		$p->showError("$label ".nl2br($message)." \n\n\n<!-- Error level $n. Stack trace:\n$trace -->");
	} else {
		if (headers_sent()) {
			// output already started, the best we can do is print generic html
			// FIXME assuming html. should be different output if response is text/plain
			echo("<strong>$label</strong> ".nl2br($message)."\n\n\n<!-- Error level $n. Stack trace:\n$trace -->\n\n");
		} else {
			// if output is buffered, we can clear the buffer and print an error message instead
			if (ob_get_contents()) {
				ob_clean();
			}
			// Same status code as text-only response
			reportErrorStatus($n, $message, $trace);
			// to make the status headers work we can't do redirect
			$p->showErrorNoRedirect("$label ".nl2br($message)."\n\n\n<!-- Error level $n. Stack trace:\n$trace -->\n\n");
		}
	}
}}

?>
