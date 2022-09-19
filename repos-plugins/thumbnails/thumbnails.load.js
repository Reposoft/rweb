
// This list was for Repos <1.4, is now depending on the transforms defined in convert service
Repos.thumbnails = {
	filetypes: 'jpe?g|png|gif'+
		'|bmp|eps|pdf|ps|psd|ico|svg|tif|tiff'+
		'|avi'+
		'|cgm'+ // users that need CGM must install delegate RalCGM for ImageMagick or GraphicsMagick
		'|ai'+ // some adobe formats are actually pdf or postscript
		''
};

/**
 * @deprecated Try thumbnails service instead and abort on status 415
 */
Repos.thumbnails.match = new RegExp('\.(' + Repos.thumbnails.filetypes + ')$', 'i');

/**
 * Identify the intro section (in current thisArg) for details/edit page thumbnail
 */
Repos.thumbnails.addThumbnailToIntro = function() {
	var parent = $('#intro', this);
	if (parent.size()==0) return;
	Repos.thumbnails.addThumbnail(parent);
};

Repos.service('open/', Repos.thumbnails.addThumbnailToIntro);
Repos.service('edit/', Repos.thumbnails.addThumbnailToIntro);

/**
 * Prepend thumbnail tag to jQuery element(s)
 */
Repos.thumbnails.addThumbnail = function(parent) {
	var target = Repos.getTarget();
	var rev = Repos.getRevision();
	var revIsPeg = Repos.isRevisionRequested(); // do we always display a "changed" revision or might it be rev < requested?
	var src = Repos.thumbnails.getSrc(target, rev, revIsPeg);
	if (!src) return;
	var img = $("<img />").addClass('thumbnail').attr('src', src).load(function() { // alt="Creating thumbnail..."
		if (!this.complete
				|| typeof this.naturalWidth == "undefined"
				|| this.naturalWidth == 0) {
			window.console && console.warn('No error message, no image', src);
		} else {
			parent.prepend(img);
		}
	}).error(function() {
		// normally status=415, error not called for status=500 which is good because we want to show the error thumbnail instead
		$(this).hide();
	});
};

/**
 * Get the URL to thumbnail
 * @param target {String} the path
 * @param rev {String|int} operating revision, if not HEAD it must be an existing revision at the given path.
 *  Revision MUST be a changed revision (i.e. one in the target's log) for efficient caching.
 * @param revIsPeg {boolean} false if target url is at HEAD, true if target url is at rev
 */
Repos.thumbnails.getSrc = function(target, rev, revIsPeg) {
	if (!target) return false;
	var repository = Repos.getRepository();
	var url;
	if (repository) {
		url = repository + encodeURI(target) + '?rweb=t.thumb';
	} else {
		url = '/repos-plugins/thumbnails/convert/?target=';
		url = url + encodeURIComponent(target);
		url = url + '&base=' + Repos.getBase();
	}
	if (typeof rev != 'undefined' && rev) {
		url = url + (revIsPeg ? '&p=' : '&r=') + rev;
	} else {
		window.console && console.warn('Revision not set for thumbnail. Caching will be disabled.');
	}
	return url;
};
