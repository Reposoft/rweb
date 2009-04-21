<?php
/**
 * We want to be able to install all libraries with CURL (not javascript)
 *
 * @package
 */
set_time_limit(60*5); // normally set by Report.class.php, but we don't use that include here 
 
require('../open/ServiceRequest.class.php');

header('Content-Type: text/plain');

function out($text) { echo $text; flush(); }

// library ids are same as folder names
$libraries = array(
'smarty',
'simpletest',
'selenium',
'syntaxhighlighter',
'tinymce',
//'phpicalendar',
//'imagemagick'
);

$url = getWebapp().'lib/';

$params = array();
if (array_key_exists('go', $_GET)) $params['go'] = '1';

out("{\n");
foreach($libraries as $lib) {
	$installurl = "{$url}$lib/install.php";
	out("$lib: {name:\"$lib\", install:\"$installurl\", ");
	$s = new ServiceRequest($installurl, $params, false);
	if ($s->exec()!=200) {
		$c = $s->getStatus();
		out("error:".($c ? $c : '"no response"')." },\n");
		continue;
	}
	out("installed:");
	if (preg_match('/[Dd]one/', $s->getResponse())) {
		out("true");
	} else {
		out("false");
	}
	out("},\n");
}
out("}\n");

?>

