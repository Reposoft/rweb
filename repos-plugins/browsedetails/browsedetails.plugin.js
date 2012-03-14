
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
	
	var isActive = function() {
		return $('.index').is('.view-list');
	};
	
	/**
	 * Adds visual indications and/or event handling to activate details loading.
	 * @param {jQuery} target The element(s) for which to allow details opened
	 * @param callback {Function} the function to call when details should be loaded for "this"
	 */
	var captureIntent = function(targetElement, callback) {
		return targetElement.each(function() {
			var a = $(this);
			var row = a.parent();
			var yes = function(ev) {
				if (!isActive) return;
				var current = $('.browsedetail'); 
				if (row.is(ev.target) && !row.is(current)) {
					current.removeClass('browsedetail');
					row.addClass('browsedetail');
					callback.call(a);
				}
			};
			var no = function(ev) {
			};
			row.hoverIntent({over: yes, out: no, timeout: 200, interval: 1500, sensitivity: 20, evIn: 'mouseover', evOut: 'mouseout', withChildren: false});
			row.click(yes);
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
		container.html(html);
		var intro = $('#intro', container).css({margin: 0, padding: 0});
		$('h1 a', intro).css('background-position', 'left .25em');
		$('a', container).each(function() {
			$(this).filter('#open:contains("List")').remove(); // or should we change it to normal index for use from search results?
			var href = $(this).attr('href');
			if (href.charAt(0) != '/' && href.indexOf('://') < 0) {
				$(this).attr('href', '/repos-web/open/' + $(this).attr('href'));
			}
			if (this.id == 'realurl') {
				this.id = 'embedurl'; // realurl already exists in parent page
				console.log('detailify', this);
				var real = href;
				$(this).attr('href', real + '?rweb=details'); // TODO support ?p/r
				var text = $('dd.aboutitem-path', container).text();
				if (text.length > 63) text = '...' + text.substr(text.length - 60);
				$('<a/>').addClass("path").attr('href', href).text(text).prependTo(container);
			}
		});
	};
	
	var addCloseButton = function(container) {
		var b = $('<div>x</div>').addClass('closebutton').prependTo(container).click(function() {
			$('.browsedetail').removeClass('browsedetail');
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
						container.addClass('preview');
						$(".column-info", container).prepend(preview.append(img));
					}
				}).error(function() {
					// normally status=415, error not called for status=500 which is good because we want to show the error thumbnail instead
					$(this).hide(); 
				});
	};
	
	var asEmbeddedHtml = function() {
		var a = $(this);
		var isFile = a.is('.file');
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
			$('.datetime', container).dateformat();
			if (isFile)	addThumbnail(href, container);
			Repos.asyncService('open/', $('.path', container).text(), container);
		});
	};
	
	// run
	return captureIntent(this, asEmbeddedHtml);
	
};
	
Repos.service('index/', function() {
	$('.file, .folder', $('.index')).reposDetailsTarget({});
});

})(jQuery);

/**
* hoverIntent is similar to jQuery's built-in "hover" function except that
* instead of firing the onMouseOver event immediately, hoverIntent checks
* to see if the user's mouse has slowed down (beneath the sensitivity
* threshold) before firing the onMouseOver event.
* 
* hoverIntent r6 // 2011.02.26 // jQuery 1.5.1+
* <http://cherne.net/brian/resources/jquery.hoverIntent.html>
* 
* hoverIntent is currently available for use in all personal or commercial 
* projects under both MIT and GPL licenses. This means that you can choose 
* the license that best suits your project, and use it accordingly.
* 
* // basic usage (just like .hover) receives onMouseOver and onMouseOut functions
* $("ul li").hoverIntent( showNav , hideNav );
* 
* // advanced usage receives configuration object only
* $("ul li").hoverIntent({
*	sensitivity: 7, // number = sensitivity threshold (must be 1 or higher)
*	interval: 100,   // number = milliseconds of polling interval
*	over: showNav,  // function = onMouseOver callback (required)
*	timeout: 0,   // number = milliseconds delay before onMouseOut function call
*	out: hideNav    // function = onMouseOut callback (required)
* });
* 
* @param  f  onMouseOver function || An object with configuration options
* @param  g  onMouseOut function  || Nothing (use configuration options object)
* @author    Brian Cherne brian(at)cherne(dot)net
* 
* Updated by Staffan Olsson, www.repos.se
*  - Event names as config,
*  - A class to mark the candidate element
*  - Event only handled as enter if the element is the event target (not children)
*/
(function($) {
	$.fn.hoverIntent = function(f,g) {
		// default configuration options
		var cfg = {
			sensitivity: 7,
			interval: 100,
			timeout: 0,
			withChildren: true,
			evIn: 'mouseenter',
			evOut: 'mouseleave',
			evMove: 'mousemove',
			mark: 'intent-maybe'
		};
		// override configuration options with user supplied object
		cfg = $.extend(cfg, g ? { over: f, out: g } : f );

		// instantiate variables
		// cX, cY = current X and Y position of mouse, updated by mousemove event
		// pX, pY = previous X and Y position of mouse, set by mouseover and polling interval
		var cX, cY, pX, pY;

		// A private function for getting mouse position
		var track = function(ev) {
			cX = ev.pageX;
			cY = ev.pageY;
		};

		// A private function for comparing current and previous mouse position
		var compare = function(ev,ob) {
			ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
			// compare mouse positions to see if they've crossed the threshold
			if ( ( Math.abs(pX-cX) + Math.abs(pY-cY) ) < cfg.sensitivity ) {
				$(ob).unbind(cfg.evMove,track);
				// set hoverIntent state to true (so mouseOut can be called)
				ob.hoverIntent_s = 1;
				return cfg.over.apply(ob,[ev]);
			} else {
				// set previous coordinates for next time
				pX = cX; pY = cY;
				// use self-calling timeout, guarantees intervals are spaced out properly (avoids JavaScript timer bugs)
				ob.hoverIntent_t = setTimeout( function(){compare(ev, ob);} , cfg.interval );
			}
		};

		// A private function for delaying the mouseOut function
		var delay = function(ev,ob) {
			ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t);
			ob.hoverIntent_s = 0;
			return cfg.out.apply(ob,[ev]);
		};

		// A private function for handling mouse 'hovering'
		var handleHover = function(e) {
			// copy objects to be passed into t (required for event object to be passed in IE)
			var ev = jQuery.extend({},e);
			var ob = this;

			// cancel hoverIntent timer if it exists
			if (ob.hoverIntent_t) { ob.hoverIntent_t = clearTimeout(ob.hoverIntent_t); }

			// if e.type == "mouseenter"
			if (e.type == cfg.evIn && (cfg.withChildren || $(ob).is(e.target))) {
				// set "previous" X and Y position based on initial entry point
				pX = ev.pageX; pY = ev.pageY;
				// update "current" X and Y position based on mousemove
				$(ob).bind(cfg.evMove,track);
				// start polling interval (self-calling timeout) to compare mouse coordinates over time
				if (ob.hoverIntent_s != 1) { ob.hoverIntent_t = setTimeout( function(){compare(ev,ob);} , cfg.interval );}
				if (cfg.mark) {
					ob.hoverIntent_ct = setTimeout(function(){$(ob).addClass(cfg.mark);}, cfg.timeout); 
				}
				
			// else e.type == "mouseleave"
			} else {
				// unbind expensive mousemove event
				$(ob).unbind(cfg.evMove,track);
				// if hoverIntent state is true, then call the mouseOut function after the specified delay
				if (ob.hoverIntent_s == 1) { ob.hoverIntent_t = setTimeout( function(){delay(ev,ob);} , cfg.timeout );}
				if (cfg.mark) {
					clearTimeout(ob.hoverIntent_ct);
					$(ob).removeClass(cfg.mark);
				}
			}
		};

		// bind the function to the two event listeners
		return this.bind(cfg.evIn,handleHover).bind(cfg.evOut,handleHover);
	};
})(jQuery);
