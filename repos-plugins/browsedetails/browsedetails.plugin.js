
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
		var extend = $('<span>&raquo;</span>').addClass('itemextend').appendTo($(targetElement).parent());
		extend.click(function() {
			callback.call($(this).parent().find('a:first'));
		});
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
			window.console && window.console.error('Content extraction failed from html: ', html);
			return;
		}
		html = from[1];
		html = html.replace('id="realurl" class="file"', 'id="realurl"');
		html = html.replace(/<div id="footer".*/, '');
		html = html.replace(/<p[^<]*(<a[^<]*<\/a>)?[^<]*<\/p>/g,'');
		html = html.replace(/<dl class="properties.*\/dl>/, '');
		html = html.replace(/href="/g, 'href="/repos-web/open/');
		container.html(html);
	};
	
	var addCloseButton = function(container) {
		var b = $('<div>x</div>').css({
			color: '#999',
			cursor: 'pointer',
			fontSize: '120%'
		}).prependTo(container).click(function() {
			container.empty();
		});
	};
	
	var addThumbnail = function(href, container) {
		var thref = href.replace('?rweb=details', '?rweb=t.thumb');
		var img = $("<img />").addClass('thumbnail').attr('src', thref).load(function() {
					if (!this.complete
							|| typeof this.naturalWidth == "undefined"
							|| this.naturalWidth == 0) {
						window.console && console.warn('No error message, no image', thref);
					} else {
						$("#intro", container).prepend(img);
					}
				}).error(function() {
					$(this).hide();
					window.console && console.log('Image service failed', thref);
				});
	};
	
	/**
	 * Alternative concept loading an iframe in a jQuery UI dialog, using
	 * "embed" mode
	 */
	var asIframePopup = function() {
		var item = $(this);
		var href = getDetailsUrl(this) + '&serv=embed';
		var title = item.text();
		var extendbox = $('<div/>').addClass('extendbox');
		extendbox
			.html('<iframe src="' + href + '" width="100%" height="100%"></iframe>')
        	.dialog({
	            autoOpen: false,
	            modal: false,
	            height: 500,
	            width: '55em',
	            title: title
	        });
        extendbox.dialog('open');		
	};
	
	var asEmbeddedHtml = function() {
		var href = getDetailsUrl(this);
		var container = $(settings.container);
		var topstart = $('.index').offset().top;
		var topscroll = $(document).scrollTop();
		container.empty().css('margin-top', topscroll > topstart ? Math.floor(topscroll - topstart + 10) : 0);
		container.html('<img border="0" src="/repos-web/style/loading.gif"/>');
		reposDetailsLoad(href, function(html) {
			reposDetailsInsert(html, container);
			addCloseButton(container);
			addThumbnail(href, container);
		});
	};
	
	// run
	//return captureIntent(this, asIframePopup);
	return captureIntent(this, asEmbeddedHtml);
	
};
	
Repos.service('index/', function() {
	if ($('body').is('.static')) return;
	$('.file, .folder').reposDetailsTarget({});
});

})(jQuery);
