// IE6 is buggy on back and refresh if there are non-ascii characters in query string
// which we can not avoid when using SVNIndexXSLT
if ($.browser.msie && $.browser.version == '6.0') {
	var s = window.location.search.replace(/[\u0080-\uFFFF]+/g, function(s) {
		return encodeURI(s);
	});
	if (s && s != window.location.search) window.location = window.location.pathname + s;
}

// Repos.service('index/', /* could also replace target value in all links to avoid redirect, but with the service layer it won't be nessecary */);
