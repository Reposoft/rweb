// http://github.com/zuk/jquery.inview
/**
 * author Remy Sharp
 * url http://remysharp.com/2009/01/26/element-in-view-event-plugin/
 */
// Customized by Staffan Olsson for the use case .one('inview', callback-on-true).trigger(scroll)
(function ($) {
    function getViewportHeight() {
        var height = window.innerHeight; // Safari, Opera
        var mode = document.compatMode;

        if ( (mode || !$.support.boxModel) ) { // IE, Gecko
            height = (mode == 'CSS1Compat') ?
            document.documentElement.clientHeight : // Standards
            document.body.clientHeight; // Quirks
        }

        return height;
    }

    function getScrolltop() {
    	return document.documentElement.scrollTop ?
                document.documentElement.scrollTop :
                document.body.scrollTop;
    }
    
    // values during bind phase. will be overwritten at scroll.
    var vpH = false, scrolltop = false;
    
    // to be done at first bind
    function init() {
    	if (vpH === false) vpH = getViewportHeight();
    	if (scrolltop === false) scrolltop = getScrolltop();
    }
    
    // TODO for our simple use case with event handling only on first inview=true
    // there is no need to keep track of state
    function checkInview() {
        var $el = $(this),
            top = $el.offset().top,
            height = $el.height(),
            inview = $el.data('inview') || false;

        if (scrolltop > (top + height) || scrolltop + vpH < top) {
            if (inview) {
                $el.data('inview', false);
                $el.trigger('inview', [ false ]);                        
            }
        } else if (scrolltop < (top + height)) {
            if (!inview) {
                $el.data('inview', true);
                $el.trigger('inview', [ true ]);
            }
        }
    }
    
    // bind one, check immediately
    $.fn.inviewOne = function(callback) {
    	init();
    	this.one('inview', callback);
    	// doing this for each bind is too slow (requires "continue" in firefox for 1000 images) //checkInview.apply(this);
    };
    
    // TODO use elements from inviewOne instead of $.cache for efficiency
    // and remove element from the list upon first inview
    $(window).scroll(function () {
    	// overwrite current values in checkInview's closure scope
        vpH = getViewportHeight();
        scrolltop = getScrolltop();
        var elems = [];
        
        // naughty, but this is how it knows which elements to check for
        $.each($.cache, function () {
            if (this.events && this.events.inview) {
                elems.push(this.handle.elem);
            }
        });

        if (elems.length) {
            $(elems).each(checkInview);
        }
    });
    
})(jQuery);

