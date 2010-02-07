
Repos.thumbnails = {
	filetypes: 'jpg|png|gif'+
		'|bmp|eps|pdf|ps|psd|ico|svg|tif|tiff'+
		'|avi'+
		'|ai'+ // some adobe formats are actually pdf or postscript
		'' 
};

Repos.thumbnails.match = new RegExp('\.(' + Repos.thumbnails.filetypes + ')$', 'i');

Repos.target(Repos.thumbnails.match, function() {
	if ($('#intro').size()==0) return;
	// Repos.getTarget et.al. does not provide a getRevision
	if (window.location.search.length==0) return;
	var href = window.location.search;
	var target = Repos.getTarget();
	var rev = Repos.getRevision();
	var src = Repos.thumbnails.getSrc(target, rev);
	if (!src) return;
	$('#intro').prepend('<img class="thumbnail" src="'+src+'" alt="Creating thumbnail..." border="0"/>');
	$('#intro').append('<div style="clear: both;"></div>'); 
});

Repos.thumbnails.getSrc = function(target, rev) {
	if (!target) return false;
	var url = '/repos-plugins/thumbnails/convert/?target=';
	url = url + encodeURIComponent(target);
	url = url + '&base=' + Repos.getBase();
	if (typeof rev != 'undefined' && rev) {
		url = url + '&rev=' + rev;
	} else {
		console && console.warn('Revision not set for thumbnail. Caching will be disabled.');
	}
	return url;
};
