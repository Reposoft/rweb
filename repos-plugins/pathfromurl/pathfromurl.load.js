// Allow URLs to be pasted in form fields that are used for paths
(function() {

	var currentRepository = Repos.getRepository();
	
	// return path if url argument is a path inside current repository
	var getPathFromUrl = function(url) {
		if (url==null) {
			return;
		}
		if (window.unescape) {
			url = window.unescape(url);
		}
		if (url.indexOf('https')==0 && currentRepository.indexOf('http:')==0) {
			r = currentRepository.replace(/http:/,"https:");
		} else {
			r = currentRepository;
		}
		if (url.indexOf(r)!=0 && /\w+:\/\//.test(url)) {
			alert('The folder URL must begin with the repository URL '+r);
			return;
		}
		return url.substring(r.length);
	};

	var activate = function() {
		$('input.path.folder').blur(function() {
			var path = getPathFromUrl($(this).val());
			path && $(this).val(path);
		});
	};
	
	// TODO restrict what pages to do this in, for performance?
	$(document).ready(activate);

})();