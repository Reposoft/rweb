// IE6 is buggy on back and refresh if there are non-ascii characters in query string
// which we can not avoid when using SVNIndexXSLT
if ($.browser.msie && $.browser.version == '6.0') {
	/* breaks back button
	var s = window.location.search.replace(/[\u0080-\uFFFF]+/g, function(s) {
		return encodeURI(s);
	});
	if (s && s != window.location.search) window.location = window.location.pathname + s;
	*/
	// modify index so all targets that include the /svn/index/@path are encoded
	Repos.service('index/', function() {
		if (/[\u0080-\uFFFF]/.test(decodeURI(window.location.href))) { // optimization
			// check all targets for utf8 characters
			$('a').each(function() {
				var a = $(this);
				var h = a.attr('href');
				if (!h) return;
				var q = h.match(/([?&]target=)([^&]+)/);
				if (!q || !q[2]) return;
				var r = q[2].replace(/[\u0080-\uFFFF]+/g, function(s) {
					return encodeURI(s);
				});
				a.attr('href',h.replace(q[0],q[1]+r));
			});
		}
	});
}
