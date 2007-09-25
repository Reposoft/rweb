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
// entries must have unique names in json
$xml = preg_replace('/<entry\s+kind="(file|dir)">\s+<name>(.*)<\/name>(.*)<\/entry>/sU','"\2":{\3kind:"\1"},',$xml);
// elements with no attributes
$xml = preg_replace('/<(\w+)>(.*)<\/\1>\s*/','\1:"\2",',$xml);
// elements with one attribute
$xml = preg_replace('/<(\w+)\s+(\w+)="(\d+)">(.*)<\/\1>/sU','\1:{\4\2:"\3"},',$xml);
// define the svn variable that wraps it up
$xml = preg_replace('/.*<list\s+path="([^"]+)">/s','svn = {path:"\1", list:{',$xml);
$xml = str_replace(",\n</list>\n</lists>","\n}};",$xml);

// fist part of the page, just print the svnlist json
header('Content-Type: text/javascript; charset=utf-8');
header('Content-Length: '.strlen($xml));
echo($xml);

// second part of the script is printed if there is a selector
if (!isset($_REQUEST['selector'])) exit;

$selector = "'".$_REQUEST['selector']."'";

?>

console.log(svnlist);
// Subversion service layer AJAX contents, requres jQuery

(function () {
	// this method was designed for XML from the beginning, currently not up to date
	var svn_list = svn.list;

	// query string parameters to the script source used to customize behaviour
	var params = [];
	
	// get the URL for this script, which is the same folder as the contents it lists
	var scripts = document.getElementsByTagName('script');
	for (i=scripts.length-1; i>=0; i--) {
		if (!scripts[i].src) continue;
		var m = /^(.*\/)\?svn=listjs&?(.*)$/.exec(scripts[i].src);
		if (m && m.length > 1) {
			if (m[2]) params = eval('({'+m[2].replace(/=/g,':"').replace(/&/g,'",')+'"})');
			params.path = m[1];
			break;
		}
	}
	if (!params.path) { console.log('script path not found'); return; }
	
	// default settings, override with custom settings
	var settings = $.extend({
      selector: '#reposlist',
      titles: true,
      path: null
	}, params);

	//todo: write directly to body (no selector) if parent is not head
	$().ready( function() {
		var xml = parseXml(svn_list_xml); // domify xml string
		$('/lists/list/entry', xml).each( function() {
			var name = $('name', this).text();
			var href = settings.path + name;
			$(settings.selector).append('<li class="svnlist"><ul class="'+$(this).attr('kind')+'">'
				+'<li class="name"><a href="'+href+'">'+name+'</a></li>'
				+'<li class="revision">'+$('commit', this).attr('revision')+'</li>'
				+'<li class="user">'+$('commit/author', this).text()+'</li>'
				+'<li class="datetime">'+$('commit/date', this).text()+'</li>'
				+'</ul></li>');
		} );
	} );
}).call();
