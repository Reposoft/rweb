
(function($){

/**
 * Activates the possibility to load an embedded details page for repos target (file and folder link) elements.
 */
$.fn.reposDetailsTarget = function(options) {
	
	var settings = $.extend({
		/**
		 * {string} Selector for the element where details HTML should be written.
		 */
		container: '.contentdetails',
		error: function(req) {
			alert('Details load error ' + 
					(typeof req == 'undefined' ? '' : req.status));
		}
	}, options);
	
	/**
	 * Adds visual indications and/or event handling to activate details loading.
	 * @param {jQuery} target The element(s) for which to allow details opened
	 * @param callback {Function} the function to call when details should be loaded for "this"
	 */
	var captureIntent = function(targetElement, callback) {
		return targetElement.each(function() {
			var a = $(this);
			var row = a.parent(); 
			row.click(function(ev) {
				if (row.is(ev.target)) callback.call(a);
			});
		});
		$(targetElement)
		return targetElement;
	};
	
	var getContainer = function(targetElement) {
		return $(settings.container);
	};
	
	var getDetailsUrl = function(targetElement) {
		// The subversion URL of the file or folder, possibly with ?p/r param, possibly with ?rweb=details
		var href = $(targetElement).attr('href');
		// TODO support ?p/r
		if (href.indexOf('?') == -1) { // folders typically
			href += '?rweb=details';
		}
		return href;
	};
	
	/**
	 * @param {string} detailsHref The URL to the details page to be loaded
	 * @param {function} callback The content callback, jQuery AJAX success handler, taking XHTML
	 */
	var reposDetailsLoad = function(detailsHref, callback) {
		$.ajax({
			url: detailsHref,
			dataType: callback.dataType || 'html',
			success: callback,
			error: settings.error
		});
	};
	
	var reposDetailsInsert = function(html, container) {
		// Handling page as string because DOM handling from AJAX response didn't work
		// This is very sensitive to details page markup changes
		html = html.replace(/\r?\n/g,'');
		var from = html.match(/.*(<div id="intro".*)/);
		if (!from) {
			container.html('<p class="error">Failed to load details</p>');
			console.error('Content extraction failed from html: ', html);
			return;
		}
		html = from[1];
		html = html.replace(/<div id="footer".*/, '');
		html = html.replace(/<p[^<]*(<a[^<]*<\/a>)?[^<]*<\/p>/g,'');
		html = html.replace(/<dl class="properties.*\/dl>/, '');
		html = html.replace(/href="/g, 'href="/repos-web/open/');
		container.html(html);
		var intro = $('#intro', container).css({margin: 0, padding: 0});
		$('h1 a', intro).css('background-position', 'left .25em');
	};
	
	var addCloseButton = function(container) {
		var b = $('<div>x</div>').addClass('closebutton').prependTo(container).click(function() {
			container.empty();
		});
	};
	
	var addThumbnail = function(href, container) {
		var thref = href.replace('?rweb=details', '?rweb=t.preview');
		var preview = $('<div/>').addClass('preview').css({marginLeft: 5, marginRight: 5, textAlign: 'center'});
		var img = $("<img />").addClass('thumbnail').attr('src', thref).load(function() {
					if (!this.complete
							|| typeof this.naturalWidth == "undefined"
							|| this.naturalWidth == 0) {
						console.warn('No error message, no image', thref);
					} else {
						$(".column-info", container).prepend(preview.append(img));
					}
				}).error(function() {
					// normally status=415, error not called for status=500 which is good because we want to show the error thumbnail instead
					$(this).hide(); 
				});
	};
	
	var asEmbeddedHtml = function() {
		var isFile = $(this).is('.file');
		var href = getDetailsUrl(this);
		var container = $(settings.container);
		var topstart = $('.index').offset().top;
		var topscroll = $(document).scrollTop();
		container.css('opacity', '.3').css('margin-top', topscroll > topstart ? Math.floor(topscroll - topstart + 10) : 0);
		if (container.is(':empty')) {
			container.html('<img border="0" src="/repos-web/style/loading.gif"/>'); // need to get dimensions
		} else {
			container.addClass('loading');
		}
		reposDetailsLoad(href, function(html) {
			container.css('opacity', 'inherit').removeClass('loading');
			reposDetailsInsert(html, container);
			addCloseButton(container);
			if (isFile)	{
				$('#editlog', container).hide();
				addThumbnail(href, container);
			}
		});
	};
	
	// run
	//return captureIntent(this, asIframePopup);
	return captureIntent(this, asEmbeddedHtml);
	
};
	
Repos.service('index/', function() {
	$('.file, .folder', $('.index')).reposDetailsTarget({});
});

})(jQuery);
