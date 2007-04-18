<?php
/**
 * Compile smarty templates to cache folder.
 */

/* don't show runtime errors, we don't expect the templates to evaluate properly
function reportErrorToUser($n, $message, $trace) {
} */

// for source protection, templates will be removed if they contain the following string (which should be true for all important pages)
define('TEMPLATE_IDENTIFY', '<!--{$head}-->');

$basedir = dirname(dirname(dirname(__FILE__))).'/';

$template = null;

if ( isset($argv) && count( $argv ) == 2 ) $template = $argv[1];
if ( isset($_REQUEST['template'])) $template = $_REQUEST['template'];

if (!isset($template)) die("Parameter 'template' (first command line argument) not set.");

// must be the exact same cache name used in runtime
$given = $template;
$template = $basedir . $template;
$template = strtr($template, '\\', '/');
if (!file_exists($template)) die("Template '$template' not found.");

// use the presentation class to get custom processing
require($basedir.'conf/Presentation.class.php');

$result = ''.$template;

// use Presentation constructor to initialize template filters
$p = new Presentation();
$s = $p->smarty;

// mimic some internals of smarty fetch function to produce the cached template
$s_path = $s->_get_compile_path($template);
if ($s->_compile_resource($template, $s_path)) {
	$result .= " \tOK";
	clearOriginal($template);
} else {
	$result .= " \tFailed";
}

echo($result);

function clearOriginal($path) {
	global $given;
	// truncate original file (there is a bug in smarty, so it can't be deleted even if compile_check is disabled)
	$fh = fopen($path,'r');
	$contents = fread($fh, 32768);
	fclose($fh);
	if (strpos($contents, TEMPLATE_IDENTIFY) === false) return;
	$fh = fopen($path,'w');
	fwrite($fh, "Error: could not read compiled template '$given'.");
	fclose($fh);
}

?>
