
(function($){

/**
 * Activates the possibility to load an embedded details page for repos target (file and folder link) elements.
 */
$.fn.reposDetailsTarget = function(options) {
	
	var settings = $.extend({
		/**
		 * {string} Selector for the element where details HTML should be written.
		 */
		container: '.contentdetails'
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
		
	};
	
	var reposDetailsInsert = function(html, container) {
		
	};
	
	/**
	 * Alternative concept loading an iframe in a jQuery UI dialog, using "embed" mode
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
		console.log('show details ', this);
	};
	
	// run
	return captureIntent(this, asIframePopup);
	//return captureIntent(this, asEmbeddedHtml);
	
};
	
Repos.service('index/', function() {
	$('.file, .folder').reposDetailsTarget({});
});

})(jQuery);
