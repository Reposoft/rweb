<?php
/**
 * Server side generated javascript settings.
 * Declares predefined settings variables.
 * Organizes the plugin imports.
 * 
 * Note that the generated script should not be cached, so keep it small.
 */
$revision = '$Rev$';

// use nocache headers, or does repos.properties.php do that?

require(dirname(dirname(dirname(__FILE__))).'/conf/repos.properties.php');

$repos_web = getConfig('repos_web');

// load same plugins in all pages
// could also use a properties file in all plugins 
$plugins = array( 
'dateformat'
);

$nocache = rand();

// done, write repos.js to the js
// first override the commonly used createElement wirt createElementNS where required (like firefox with XML docs)
?>
if (document.documentElement && document.documentElement.namespaceURI && document.createElementNS) {
	document.createElement = function(t) {
		return document.createElementNS(document.documentElement.namespaceURI, t);
	};
}
var _repos_head = document.getElementsByTagName('head')[0];
function repos_addScript(src) {
	var s = document.createElement('script');
	s.type = "text/javascript";
	s.src = src;
	document.getElementsByTagName('head')[0].appendChild(s);
}
<?php
echo("repos_addScript('$repos_web/scripts/repos.js?$nocache');\n");
?>
function _repos_load() {
<?php
// load the other plugins
// write settings to the js
for($i=0; $i<count($plugins); $i++) {
	echo("repos_addScript('$repos_web/plugins/$plugins[$i]/$plugins[$i].js?$nocache');");
}
?>
}
function _repos_init() {
	if (typeof($)=='undefined') {
		window.setTimeout('_repos_init()', 10);
	} else {
		_repos_load();
	}
}
_repos_init();
