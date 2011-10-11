
Repos.thumbnails = {
	filetypes: 'jpg|png|gif'+
		'|bmp|eps|pdf|ps|psd|ico|svg|tif|tiff'+
		'|avi'+
		'|cgm'+ // users that need CGM must install delegate RalCGM for ImageMagick or GraphicsMagick
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
	var revIsPeg = Repos.isRevisionRequested(); // do we always display a "changed" revision or might it be rev < requested?
	var src = Repos.thumbnails.getSrc(target, rev, revIsPeg);
	if (!src) return;
	$('#intro').prepend('<img class="thumbnail" src="'+src+'" alt="Creating thumbnail..." border="0"/>');
	$('#intro').append('<div style="clear: both;"></div>'); 
});

/**
 * Get the URL to thumbnail
 * @param target {String} the path
 * @param rev {String|int} operating revision, if not HEAD it must be an existing revision at the given path.
 *  Revision MUST be a changed revision (i.e. one in the target's log) for efficient caching.
 * @param revIsPeg {boolean} false if target url is at HEAD, true if target url is at rev
 */
Repos.thumbnails.getSrc = function(target, rev, revIsPeg) {
	if (!target) return false;
	var url = '/repos-plugins/thumbnails/convert/?target=';
	url = url + encodeURIComponent(target);
	url = url + '&base=' + Repos.getBase();
	if (typeof rev != 'undefined' && rev) {
		url = url + (revIsPeg ? '&p=' : '&r=') + rev;
	} else {
		console && console.warn('Revision not set for thumbnail. Caching will be disabled.');
	}
	if (typeof peg != 'undefined' && peg) {
		url = url + '&peg=' + peg;
	}
	return url;
};
