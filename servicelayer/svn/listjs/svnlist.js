/* 
List svn folder contents (c) 2007 Staffan Olsson

Requires 
See also http://feather.elektrum.org/book/src.html

Usage:
Add a copy of jquery from http://jquery.com/ to the page
Put this script in the same folder as the files and add to page using
Include this script with optional query parameters from the same host
*/

(function () {
	// query string parameters to the script source used to customize behaviour
	var params = null;
	
	// get the URL for this script, which is the same folder as the contents it should be listing
	var scripts = document.getElementsByTagName('script');
	for (i=scripts.length-1; i>=0; i--) {
		var m = /(.*\/)svnlist.js\??(.*)/.exec(scripts[i].src);
		if (m.length > 2) {
			params = eval('({'+m[2].replace(/=/g,':"').replace(/&/g,'",')+'"})');
			params.path = m[1];
			break;
		}
	}
	if (params==null) { console.log('script path not found'); return; }
	
	// default settings, override with custom settings
	var settings = $.extend({
      selector: '#reposlist',
      titles: true,
      path: null
	}, params);
	
	// use AJAX to but the
	$().ready( function() {
		console.log(settings.path);
		$.ajax({
			url: settings.path,
			dataType: 'xml',
			success: function(xml) {
				$('/svn/index/file', xml).each( function() {
					$(settings.selector).append('<li>'
						+this.getAttribute('name')
						+'</li>');
				} );
			}
			} );
	} );
}).call();
