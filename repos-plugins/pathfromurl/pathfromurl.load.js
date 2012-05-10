window.repos_pathfromurl = window.repos_pathfromurl || {};

// Allow URLs to be pasted in form fields that are used for paths
(function(scope) {

	scope.currentRepository = scope.currentRepository || Repos.getRepository();
	
	// return path if url argument is a path inside current repository
	scope.getPathFromUrl = function(url) {
		var r;
		if (!url || !/^https?:\/\//.test(url)) {
			return url;
		}
		if (window.unescape) {
			url = window.unescape(url);
		}
		if (url.indexOf('https:')==0 && scope.currentRepository.indexOf('http:')==0) {
			r = scope.currentRepository.replace(/http:/,"https:");
		} else {
			r = scope.currentRepository;
		}
		if (url.indexOf(r)!=0) {
			console.error('The folder URL must begin with the repository URL', r);
			return url;
		}
		return url.substring(r.length);
	};

	scope.activate = function() {
		$('.path-from-url').change(function() {
			var val = $(this).val();
			var path = scope.getPathFromUrl(val);
			if (path) {
				$(this).val(path);
			}
		});
	};
	
	$(document).ready(scope.activate);

})(window.repos_pathfromurl);
