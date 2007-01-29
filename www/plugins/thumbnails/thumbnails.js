
$(document).ready(function() {
	Thumbnails.init();
} );

Thumbnails = new Object();
Thumbnails.init = function() {
	$('.file').each(function() {
		// TODO disqualify non-images (anything that open/thumb/ can't process)
		Thumbnails.add(this);
	} );
}

Thumbnails.add = function(aTag) {
	console.log('thumbnailing image '+aTag.id);
	$(aTag).append('<img src="/repos/open/thumb/?target='+aTag.getAttribute('href')+'"/>');
}