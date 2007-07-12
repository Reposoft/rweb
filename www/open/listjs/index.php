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
 * @package open
 */
header('Content-Type: text/javascript; charset=utf-8');

require( dirname(dirname(__FILE__))."/SvnOpen.class.php" );

Validation::expect('target');
$url = getTargetUrl();

$revisionRule = new RevisionRule();
$rev = $revisionRule->getValue();

$list = new SvnOpen('list');
$list->addArgOption('--xml');
if ($rev) {
	$list->addArgUrlPeg($url, $rev);
} else {
	$list->addArgUrl($url);
}

if ($list->exec()) trigger_error('Could not read entry for URL '.$url, E_USER_ERROR);

?>
// Subversion service layer AJAX contents, requres jQuery

// temporary solution for xml string->dom, instead of failing $('<div/>').append(xml).children();
if (typeof(parseXml)=='undefined') { function parseXml(xml) {
   var dom = null;
   if (window.DOMParser) {
      try { 
         dom = (new DOMParser()).parseFromString(xml, "text/xml"); 
      } 
      catch (e) { dom = null; }
   }
   else if (window.ActiveXObject) {
      try {
         dom = new ActiveXObject('Microsoft.XMLDOM');
         dom.async = false;
         if (!dom.loadXML(xml)) // parse error ..

            window.alert(dom.parseError.reason + dom.parseError.srcText);
      } 
      catch (e) { dom = null; }
   }
   else
      alert("cannot parse xml string!");
   return dom;
} };

(function () {
	var svn_list_xml = '<?php echo implode($list->getOutput(),''); ?>';

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
