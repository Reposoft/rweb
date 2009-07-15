
Repos.formfocus = {
	first: function() {
		var f = $('input, textarea, select')
			.not('[type="hidden"]')
			.not('[disabled]')
			[0];
		if (typeof f != 'undefined') f.focus();
	}
};

(function() {
	for (i = 0; i<arguments.length; i++) {
		Repos.service(arguments[i], Repos.formfocus.first);
	}
})(	'edit/mkdir/'
	,'edit/delete/'
	,'edit/copy/'
	,'edit/rename/'
	,'edit/delete/'
	,'edit/lock/'
);