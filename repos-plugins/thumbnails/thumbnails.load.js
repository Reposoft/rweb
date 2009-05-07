
Repos.thumbnails = {
	filetypes: 'jpg|png|gif'+
		'|bmp|eps|pdf|ps|psd|ico|svg|tif|tiff'+
		'|avi'+
		'|ai'+ // some adobe formats are actually pdf or postscript
		'' 
};

Repos.thumbnails.match = new RegExp('\.(' + Repos.thumbnails.filetypes + ')$');

Repos.target(Repos.thumbnails.match, function() {
	if ($('#intro').size()==0) return;
	// Repos.getTarget et.al. does not provide a getRevision
	if (window.location.search.length==0) return;
	var href = window.location.search;
	var target = Repos.thumbnails.getTarget(href);
	var rev = Repos.thumbnails.getRev(href);
	var src = Repos.thumbnails.getSrc(target, rev);
	if (!src) return;
	$('#intro').prepend('<img class="thumbnail" src="'+src+'" alt="Creating thumbnail..." border="0"/>');
	$('#intro').append('<div style="clear: both;"></div>'); 
});

// under development
Repos.thumbnails.initRepository = function() {
	$('a.file').each(function() {
		var href = aTag.getAttribute('href');
		var target = Repos.thumbnails.getTarget(href);
		var rev = Repos.thumbnails.getRev(href);
		var src = Repos.thumbnails.getSrc(target, rev);
		if (!src) return;
		$(aTag).append('<img src="'+src+'" border="0" />');
	} );
};
Repos.thumbnails.initLog = function() {
};

Repos.thumbnails.getSrc = function(target, rev) {
	if (!target) return false;
	return '/repos-plugins/thumbnails/convert/?target='+target+'&rev='+rev+'&base='+Repos.getBase();
};

Repos.thumbnails.getTarget = function(href) {
	var m = /[\?&]target=([^&]+)/.exec(href);
	if (m && m.length > 1) return m[1];
	
	return false; // no good target found
};

Repos.thumbnails.getRev = function(href) {
	// get from query string
	var m = /[\?&]rev=([^&]+)/.exec(href);
	if (m && m.length > 1) return m[1];
	// get from details box
	var r = parseInt($('#filedetails .revision').text());
	if(!isNaN(r)) return r;
	// use HEAD, still a valid query parameter, DISABLES CACHING
	return ''; 
};
