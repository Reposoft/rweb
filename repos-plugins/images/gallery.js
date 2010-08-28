
$(document).ready(function() {

	var qs = $.deparam.querystring();
	var folder = qs.target;
	var repo = qs.base;
	
	// headline
	// TODO go to folder, not details page
	$('h2 > a').text(folder).attr('href', '/repos-web/open/?base=' + repo + '&target=' + folder);
	if (qs.base) $('h2 > a').prepend('<span>' + repo + ': </span>'); 
	
	var html = function(text) {
		return text.replace('<', '&gt;').replace('>', '&lt').replace(/\n/,'<br />');
	};
	
	// depends on imagemeta plugin
	var show = function(docs) {
		var list = $('#thumbs ul');
		var template = $('#thumbsTemplate');
		var data = [];
		for (var i = 0; i < docs.length; i++) {
			var d = docs[i];
			var filename = Repos.imagemeta.getFilename(d.id);
			var path = folder + filename;
			var src = function(transformId) {
				// gt must be first because &gt is an escape character
				return '/repos-plugins/thumbnails/convert/?gt=' + transformId + '&target=' + encodeURIComponent(path) + '&base=' + repo + '&r=' + d.svnrevision;
			};
			var image = {
					srcThumb: src('75'),
					srcOriginal: src('original'),
					title: html(d.title || filename),
					description: html(d.description || "")
			};
			image.srcScreen = src('500');
			data.push(image);
		}
		// show using microtemplate in page
		template.render(data).appendTo(list);
		// run galleriffic to transform page
		Repos.image.galleriffic();
	};
	
	var noimages = function() {
		alert('There are no indexed images in folder ' + folder);
	};
	
	Repos.imagemeta.search({
		target: folder,
		base: repo,
		rows: 1000,
		contentType: '(image/* OR application/postscript OR application/pdf)',
		success: function(solr) {
			if (solr.response.numFound) {
				show(solr.response.docs);
			} else {
				noimages();
			}
		}
	});
	
});

Repos.image = {
	galleriffic: function() {
		// We only want these styles applied when javascript is enabled
		$('div.navigation').css({'width' : '300px', 'float' : 'left'});
		$('div.content').css('display', 'block');
	
		// Initially set opacity on thumbs and add
		// additional styling for hover effect on thumbs
		var onMouseOutOpacity = 0.67;
		$('#thumbs ul.thumbs li').opacityrollover({
			mouseOutOpacity:   onMouseOutOpacity,
			mouseOverOpacity:  1.0,
			fadeSpeed:         'fast',
			exemptionSelector: '.selected'
		});
		
		// TODO Galleriffic must handle HTTP 500 errors, currently it shows the loading animation for ever
		// TODO Handle when zero images, Galleriffic produces script error
		
		// Initialize Advanced Galleriffic Gallery
		var gallery = $('#thumbs').galleriffic({
			delay:                     2500,
			numThumbs:                 15,
			preloadAhead:              2,
			enableTopPager:            true,
			enableBottomPager:         true,
			maxPagesToShow:            7,
			imageContainerSel:         '#slideshow',
			controlsContainerSel:      '#controls',
			captionContainerSel:       '#caption',
			loadingContainerSel:       '#loading',
			renderSSControls:          true,
			renderNavControls:         true,
			playLinkText:              'Play Slideshow',
			pauseLinkText:             'Pause Slideshow',
			prevLinkText:              '&lsaquo; Previous',
			nextLinkText:              'Next &rsaquo;',
			nextPageLinkText:          'Next &rsaquo;',
			prevPageLinkText:          '&lsaquo; Prev',
			enableHistory:             false,
			autoStart:                 false,
			syncTransitions:           true,
			defaultTransitionDuration: 900,
			onSlideChange:             function(prevIndex, nextIndex) {
				// 'this' refers to the gallery, which is an extension of $('#thumbs')
				this.find('ul.thumbs').children()
					.eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
					.eq(nextIndex).fadeTo('fast', 1.0);
			},
			onPageTransitionOut:       function(callback) {
				this.fadeTo('fast', 0.0, callback);
			},
			onPageTransitionIn:        function() {
				this.fadeTo('fast', 1.0);
			}
		});
	}
};

