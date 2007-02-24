<?php
/**
 * We want to be able to install all libraries with CURL (not javascript)
 *
 * @package
 */
require('../open/ServiceRequest.class.php');

header('Content-Type: text/plain');

function out($text) { echo $text; flush(); }

// library ids are same as folder names
$libraries = array(
'smarty',
'simpletest',
'selenium',
'dpsyntax',
'tinymce',
'phpicalendar',
'imagemagick'
);

$url = dirname(getSelfUrl()).'/';

$params = array();
if (array_key_exists('go', $_GET)) $params['go'] = '1';

out("{\n");
foreach($libraries as $lib) {
	$installurl = "{$url}$lib/install.php";
	out("$lib: {name:\"$lib\", install:\"$installurl\", ");
	$s = new ServiceRequest($installurl, $params, false);
	if ($s->exec()!=200) {
		out("error:".$s->getStatus()." },\n");
		continue;
	}
	out("installed:");
	if (preg_match('/done/', $s->getResponse())) {
		out("true");
	} else {
		out("false");
	}
	out("},\n");
}
out("}\n");

?>

