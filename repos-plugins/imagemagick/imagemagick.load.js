
Repos.thumbnails = new Object();

Repos.thumbnails.init  = function() {
	Repos.thumbnails.initIntro();
	// Repos.thumbnails.initRepository();
	// Repos.thumbnails.initLog();
};

Repos.thumbnails.initIntro = function() {
	if ($('#intro').size()==0) return;
	if (window.location.search.length==0) return;
	var href = window.location.search;
	var target = Repos.thumbnails.getTarget(href);
	var rev = Repos.thumbnails.getRev(href);
	var src = Repos.thumbnails.getSrc(target, rev);
	if (!src) return;
	$('#intro').prepend('<img class="thumbnail" src="'+src+'" alt="Creating thumbnail..." border="0"/>');
	$('#intro').append('<div style="clear: both;"></div>'); 
};

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
	if (!/\.(jpg|png|gif|svg)$/.test(target)) {
		return false;
	}
	return '/repos-plugins/imagemagick/convert/?target='+target+'&rev='+rev+'&base='+Repos.getBase();
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

// or should it be onload so that other resources load first?
$(document).ready(function() {
	Repos.thumbnails.init();
} );
