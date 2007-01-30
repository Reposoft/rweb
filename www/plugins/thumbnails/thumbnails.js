
$(document).ready(function() {
	Repos.thumbnails.init();
} );

Repos.thumbnails = new Object();

Repos.thumbnails.init  = function() {
	$('.file').each(function() {
		// TODO disqualify non-images (anything that open/thumb/ can't process)
		Repos.thumbnails.add(this);
	} );
}

Repos.thumbnails.add = function(aTag) {
	console.log('thumbnailing image '+aTag.id);
	var src = aTag.getAttribute('href');
	var p
	// tod handle liks to open/file/ (with target already) too
	$(aTag).append('<img src="/repos/open/thumb/?target='+aTag.getAttribute('href')+'"/>');
}

Repos.thumbnails.getTarget = function(href) {
	return '';
}

Repos.thumbnails.getRev = function(href) {
	return '';
}
