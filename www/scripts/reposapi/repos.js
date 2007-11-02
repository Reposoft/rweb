/**
 * Repos shared script logic (c) 2006 Staffan Olsson www.repos.se
 * @version $Id$
 */
var Repos = {};

/**
 * Repos selectors (c) 2007 Staffan Olsson www.repos.se
 * These selectors are the possible pointcuts for dynamically added plugins.
 * <code>
 * $(':repos-target(/admin/repos.accs)').ready( ... only if target is identical ... );
 * $(':repos-target(*.user').ready( ... only if target matches ... );
 * $(':repos-service(open/log/).ready( ... only for log service ... );
 * </code>
 */
jQuery.extend(jQuery.expr[':'], {
	'repos-target'		: 'Repos.isTarget(m[3],a)',
	'repos-service'	: 'Repos.isService(m[3],a)'
});

/**
 * Override the standard jQuery ready with one that does nothing if the selector does not match
 */
_r = jQuery.fn.ready;
jQuery.fn.ready = function(f) {
	if (this && !this.length) return;
	return _r(f);
};

// test the above case
$('body.nonexisting').ready(function() { console.warn('ready executed for empty bucket'); });
	
/**
 * Checks plugin dependencies using syntax:
 * $.depends($.differentplugin).depends($.fn.differentplugin).ready( function() {...} );
 * @param {Object, String} currently only functions are accepted, not function names
 */
// TODO make this suck less or remove it
jQuery.depends = function(func) {
	if ($.isFunction(func)) return jQuery;
	
	console.error('Repos customization error. This plugin\'s dependencies are not met.');
	// TODO return dummy so that event add is avoided?
	return new jQuery([]);
};

/**
 * Calculates webapp root based on the include path of this script (repos.js or head.js)
 * @return String webapp root url with trailing slash
 */
Repos.getWebapp = function() {
	var tags = document.getElementsByTagName("head")[0].childNodes;
	var me = /scripts\/head\.js(\??.*)$|scripts\/shared\/repos\.js$/;
	var t, n;
	for (var i = 0; i < tags.length; i++) {
		t = tags[i];
		if (!t.tagName) continue;
		n = t.tagName.toLowerCase();
		if (n == 'script' && t.src && t.src.match(me)) {// located head.js, save path for future use
			this.repos_webappRoot = t.src.replace(me, '');
		}
	}
	if (!this.repos_webappRoot) return '/repos/'; // best guess
	return this.repos_webappRoot;
};

/**
 * Static accessor for getWebapp, the application root with trailing slash
 */
Repos.url = Repos.getWebapp();

Repos.getTarget = function(context) {
	var t = $('meta[name=repos-target]');
	return t.attr('content');
};

Repos.getService = function(context) {
	var s = $('meta[name=repos-service]');
	return s.attr('content');
};

/**
 * Compares current Repos target with a pattern
 * @param {String} selector The pattern to compare with
 * @param {Object} context Optional context to get current target for
 * @return true if current target matches the selector
 */
Repos.isTarget = function(selector,context) {
	var s = selector;
	s = s.replace(/([.+^${}()\[\]\/])/g, '\\$1'); // escaping valid path characters
	s = s.replace(/\*/g,'.*');
	var r = new RegExp('^'+s+'$');
	var t = Repos.getTarget();
	var is = r.test(t);
	return is;
};

/**
 * Compares current Repos service with a pattern
 * @param {String} selector The service name, a relative path, to compare with
 * @param {Object} context
 */
Repos.isService = function(selector,target) {
	return (Repos.getService() == selector);
};

/**
 * Reads the username cookie
 * @return {String} false if user not authenticated through repos, else username
 */
Repos.getUser = function() {
	var u = $.cookie('account');
	if (u) return u;
	return false;
};

// jQuery plugin to show messages
jQuery.fn.say = function(level, text, tooltip) {
	var e = $('<span/>').addClass(level).attr('title', tooltip).html(text);
	//not good at handling page edges: //if (e.Tooltip) e.Tooltip();
	$('h1,h2,h3',this).eq(0).after(e);
	this.filter('input').parent().append(e);
}

	/*
	 Dynamic loading of scripts and css has been disabled,
	 because it was not reliable. Can be found in reposweb-1.1-B1.
	 
	 Anyway, it seems like $(document).ready() in plugins work even
	 if they are loaded dynamically. So the only limitation is that
	 3rd party libs must be loaded in page head.
	 
	 And how about $().load()?
	 */

	// -------------- plugin setup --------------
	
	/**
	 * Adds a javascript to the current page and evaluates it (asynchronously).
	 * @param src script url from repos root, not starting with slash
	 * @return the script element that was appended
	 */
	Repos.addScript = function(src, loadEventHandler) {
		// maybe it would be better to load and eval using AJAX
		var srcUrl = Repos.url + src;
		if (/:\/\/localhost[:\/]/.test(window.location.href)) srcUrl += '?'+(new Date().getTime());
		var s = document.createElement('script');
		s.type = "text/javascript";
		s.src = srcUrl;
		document.getElementsByTagName('head')[0].appendChild(s);
		return s;
	};

	/**
	 * Adds a stylesheet to the current page.
	 * @param src css url from repos root, not starting with slash
	 * @return the link element that was appended
	 * @todo is the appended css accepted by IE6?
	 */
	Repos.addCss = function(src) {
		var s = document.createElement('link');
		s.type = "text/css";
		s.rel = "stylesheet";
		s.href = Repos.url + src;
		document.getElementsByTagName('head')[0].appendChild(s);
		return s;
	};

	// ----- end plugin setup -----


// ------------ logging ------------
// firebug dummy is added to head.js: use console directly
