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
$xml = preg_replace('/,?\s*<\/list>\s+<\/lists>/',"\n}};",$xml);

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
	// '#' is a tricky character in urls, and selector will never be a tag name, so assume word means ID
	if ($k=='selector' && preg_match('/^[\w\d-]+$/',$v)) $v = '#'.$v;
	$settings .= "'$k':'$v',";
}
$settings = substr($settings,0,strlen($settings)-1).'}';

// by integrating data and script, the contents can be loaded cross-domain from page head
// "o" is the output function
$script = '
(function(jQ,url,list,set) {

	var s = jQ.extend({
		selector: "#reposlist",
	},set);

	this.o = function(class,value) {
		return jQ("<span/>").addClass(class).append(value);
	};
	
	jQ().ready( function() {
		var p = jQ(s.selector);
		for (var f in list) {
			var d = list[f];
			var e = jQ("<li/>").addClass(d.kind).appendTo(p);
			jQ("<a/>").attr("href",url+"/"+f).append(f).appendTo(e);
			e.append(o("revision",d.commit.revision));
			if (d.commit.author) {
				e.append(o("username",d.commit.author));
				e.append(o("datetime",d.commit.date));	
			} else {
				e.addClass("noaccess");
			}
			if (d.kind=="file") {
				e.append(o("filesize",d.size));
			}
			if (d.lock) {
				e.addClass("locked");
				var l = o("lock","").appendTo(e);
				l.append(o("username",d.lock.owner));
				l.append(o("datetime",d.lock.created));
				l.append(o("message",d.lock.comment));
			}
		}
	} );
})(jQuery, svn.path, svn.list, '.$settings.');
';

// simple minimize
$script = preg_replace('/^\s+/m','',$script);

header('Content-Length: '.(strlen($xml)+strlen($script)));
echo($xml);
echo($script);
