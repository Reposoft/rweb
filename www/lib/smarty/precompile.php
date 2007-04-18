<?php
/**
 * Compile smarty templates to cache folder.
 */

/* don't show runtime errors, we don't expect the templates to evaluate properly
function reportErrorToUser($n, $message, $trace) {
} */

$basedir = dirname(dirname(dirname(__FILE__))).'/';

$template = null;

if ( isset($argv) && count( $argv ) == 2 ) $template = $basedir . $argv[1];
if ( isset($_REQUEST['template'])) $template = $basedir . $_REQUEST['template'];

if (!isset($template)) die("Parameter 'template' (first command line argument) not set.");
if (!file_exists($template)) die("Template '$template' not found.");

// use the presentation class to get custom processing
require($basedir.'conf/Presentation.class.php');

$result = $template;

// use Presentation constructor to initialize template filters
$p = new Presentation();
$s = $p->smarty;

// mimic some internals of smarty fetch function to produce the cached template
$s_path = $s->_get_compile_path($template);
if ($s->_compile_resource($template, $s_path)) {
	$result .= " \tOK";
} else {
	$result .= " \tFailed";
}

echo($result);

?>
