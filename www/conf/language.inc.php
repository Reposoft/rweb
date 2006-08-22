<?php
error_reporting(E_ALL);
// key for setting selected language in coookie or query string
define('LOCALE_KEY', 'lang');
// name of this file, excluding .php
define('BASE_NAME', basename($_SERVER['PHP_SELF'],".php"));
// define the names of supported locales, as well as their respective contents file
$possibleLocales = array(
	'sv' => 'Svenska',
	'en' => 'English',
	'de' => 'Deutsch'
	);
	
// locales might require setting a cookie, which requires headers,
//  which must be sent before anything else, 
//  so we run the function directly when the file is included
getLocale();
	
/**
 * Resolve locale code from: 1: GET, 2: SESSION, 3: browser
 * @return two letter language code, lower case
 */
function getLocale() {
	global $possibleLocales;
	$locale = 'en'; 
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $locale = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	if(array_key_exists(LOCALE_KEY,$_COOKIE)) $locale = $_COOKIE[LOCALE_KEY];
	if(array_key_exists(LOCALE_KEY,$_GET)) $locale = $_GET[LOCALE_KEY];
	// validate that the locale exists
	if( !isset($possibleLocales[$locale]) )
		$locale = array_shift(array_keys($possibleLocales));
	// save and return
	if (!isset($_COOKIE[LOCALE_KEY]))  
		setcookie(LOCALE_KEY,$locale);
	else
		$_COOKIE[LOCALE_KEY] = $locale;
	return $locale;	
}

/**
 * The one-stop-shop method to get the localized version of your file.
 * @return filename of the localized version for this page
 */
function getLocaleFile($name=BASE_NAME,$extension='.html') {
	global $possibleLocales;
	$locale = getLocale();
	$chosen = getContentsFileInternal($locale,$name,$extension);
	if (file_exists($chosen)) return $chosen;
	foreach ($possibleLocales as $lo => $n) {
		$chosen = getContentsFileInternal($lo,$name,$extension);
		if (file_exists($chosen)) return $chosen;
	}
	return "file_not_found $name $locale $extension";
}


/**
 * Gets the url to change locale and access that version of this page
 * Does not preserve incoming query string
 */
function getChangeLocaleUrl($locale) {
	return BASE_NAME . '.php?' . LOCALE_KEY . '=' . $locale;
}

/**
 * Behavior can be overridden by iplementing getContentsFile($localeCode, $name=BASE_NAME, $extension='.html')
 * @return the filename representing a specified locale
 */
function getContentsFileInternal($localeCode, $name=BASE_NAME, $extension='.html') {
	if (function_exists('getContentsFile')) return getContentsFile($localeCode, $extension);
	return $name . '_' . $localeCode . $extension;
}
?>