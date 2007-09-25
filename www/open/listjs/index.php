<?php
/**
 * A combination of open/list/ and the details plugin,
 * to allow folder contents listing from any web page.
 * 
 * Add this to the html page, with optional configuration parameters
 * <code>
 * <script type="text/javascript" src="http://localhost/data/demoproject/trunk/?svn=listjs&selector=#svnlist"/>
 * ...
 * <ul id="svnlist"></ul>
 * </code>
 *
 * This functionality can be added in any svn repository folder
 * by committing a list.js script to the folder that should be listed,
 * but this dynamic approach has a few advantages:
 * - The script can be read from a different server (when in script tag)
 * - Script parameters can be used in AJAX calls too (read by php)
 * 
 * @package open
 */

require( dirname(dirname(__FILE__))."/SvnOpen.class.php" );

Validation::expect('target');
$url = getTargetUrl();

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();

$path = getTarget();

$list = new SvnOpen('list');
$list->addArgOption('--xml');
if ($rev) {
	$list->addArgUrlPeg($url, $rev);
} else {
	$list->addArgUrl($url);
}

if ($list->exec()) trigger_error('Could not read entry for URL '.$url, E_USER_ERROR);

// TODO we really need a better xml parser or xml to json
// the data must be included in the script to allow cross-domain lists

// xml is five levels deep
// maximum one attribute

// attribute never named same as child node
$xml = implode($list->getOutput(),"\n");
// define the svn variable that wraps it up
$xml = preg_replace('/.*<list\s+path="([^"]+)">/s','svn = {path:"\1", list:{',$xml);
// entries must have unique names in json
$xml = preg_replace('/<entry\s+kind="(file|dir)">\s+<name>(.*)<\/name>(.*)<\/entry>/sU','"\2":{\3kind:"\1"},',$xml);
// elements with no attributes
$xml = preg_replace('/<(\w+)>(.*)<\/\1>\s*/','\1:"\2",',$xml);
// elements with one attribute
$xml = preg_replace('/<(\w+)\s+(\w+)="(\d+)">(.*)<\/\1>/sU','\1:{\4\2:"\3"},',$xml);
// special treatment of lock, no attribute
$xml = str_replace(array('<lock>',',</lock>'),array('lock:{','},'),$xml);
// remove last comma and close object
$xml = str_replace(",\n</list>\n</lists>","\n}};",$xml);

// fist part of the page, just print the svnlist json
header('Content-Type: text/javascript; charset=utf-8');

// second part of the script is printed if there is a selector
if (!isset($_GET['selector'])) {
	header('Content-Length: '.strlen($xml));
	echo($xml);
	exit;
}

// import configuration from query string to settings json object
$settings = '{';
foreach ($_GET as $k => $v) {
	$settings .= "'$k':'$v',\n";
}
$settings .= '}';

// by integrating data and script, the contents can be loaded cross-domain from page head
$script = '
(function(jQ, url, list, settings) {

	var s = jQ.extend({
		selector: "reposlist",
	}, settings);
	// # is a tricky character in urls
	if (/\w+/.test(s.selector)) s.selector="#"+s.selector;
	
	jQ().ready( function() {
		var parent = jQ(s.selector);
		for (var f in list) {
			var en = list[f];
			var e = jQ("<li/>").addClass(en.kind).appendTo(parent);
			jQ("<a/>").attr("href",url+"/"+f).append(f).appendTo(e);
		}
	} );
})(jQuery, svn.path, svn.list, '.$settings.');
';

header('Content-Length: '.(strlen($xml)+strlen($script)));
echo($xml);
echo($script);
