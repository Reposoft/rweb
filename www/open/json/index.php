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

require('listjson.php');

Validation::expect('target');
$url = getTargetUrl();

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();

$json = getListJson($url, $rev);

// javascript output
header('Content-Type: text/plain');

// second part of the script is printed if there is a selector
if (!isset($_GET['selector'])) {
	header('Content-Length: '.strlen($json));
	echo($json);
	exit;
} else {
	// put the data in a variable for the bundled script below
	// the data must be included in the script to allow cross-domain listings
	$json = 'var svn = '.$json.';';
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
		selector: "#reposlist"
	},set);

	var o = function(c,value) {
		return jQ("<span/>").addClass("details").addClass(c).append(value);
	};

	jQ().ready( function() {
		var p = jQ(s.selector);
		for (var f in list) {
			var d = list[f];
			var e = jQ("<li/>").addClass(d.kind=="dir"?"folder":d.kind).appendTo(p);
			jQ("<a/>").attr("href",url+"/"+f+(d.kind=="dir"?"/":"")).append(f).appendTo(e);
			e.append(o("revision",d.commit.revision));
			if (d.commit.author) {
				e.append(o("username",d.commit.author));
				e.append(o("datetime",d.commit.date));
			} else {
				e.addClass("noaccess");
			}
			if (d.kind=="file") {
				e.append(o("filesize",d.size));
				var t = /\.(\w+)$/.exec(f)[1];
				if (t) e.addClass("file-"+t);
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

header('Content-Length: '.(strlen($json)+strlen($script)));
echo($json);
echo($script);
