/**
 * Repos shared script logic (c) 2006-2009 Staffan Olsson www.repos.se
 * @version $Id$
 */
var Repos = {};

// bonus, abbreviate some code patterns
jQuery.browser.sucks = jQuery.browser.msie && jQuery.browser.version == '6.0';
jQuery.browser.sucksless = jQuery.browser.msie && !jQuery.browser.sucks;

// simple non-standard indexOf fallback
if (!Array.prototype.indexOf) { Array.prototype.indexOf = function (obj, start) {
	for (var i = (start || 0); i < this.length; i++) if (this[i] == obj) return i; return -1;
};}

/**
 * DISABLED
 * Repos selectors (c) 2007 Staffan Olsson www.repos.se
 * These selectors are the possible pointcuts for dynamically added plugins.
 * <code>
 * $(':repos-target(/admin/repos.accs)').ready( ... only if target is identical ... );
 * $(':repos-target(*.user').ready( ... only if target matches ... );
 * $(':repos-service(open/log/).ready( ... only for log service ... );
 * </code>
 * Note that this can not be used with $(document).ready, use Repos.ready instead
 */
//jQuery.extend(jQuery.expr[':'], {
//	'repos-target'		: 'Repos.isTarget(m[3],a)',
//	'repos-service'	: 'Repos.isService(m[3],a)'
//});
// Note that this can not be used with $(document).ready,
// because jQuery runs ready for empty selections too,
// use Repos.ready instead
//$('body.nonexisting').ready(function() { console.warn('ready executed for empty bucket'); });

/**
 * Replaces $(document).ready since jQuery ready runs even if selector is empty.
 * @param {String} repos selector starting with :
 * @param {Function} fn for jQuery().ready
 */
Repos.ready = function(selector, fn) {
	return $('html'+selector).size() && $(document).ready(fn);
};
/**
 * Shorthand for Repos.ready(':repos-target(t)',fn)
 * @param {String} t
 * @param {Function} fn
 */
Repos.target = function(t, fn) {
	return Repos.isTarget(t) && $(document).ready(fn);
};
/**
 * Shorthand for Repos.ready(':repos-service(s)',fn)
 * @param {String} s
 * @param {Function} fn
 */
Repos.service = function(s, fn) {
	// aliases
	if (s == 'details/') s = 'open/';
	return Repos.isService(s) && $(document).ready(fn);
};

/**
 * Checks plugin dependencies using syntax:
 * $.depends($.differentplugin).depends($.fn.differentplugin).ready( function() {...} );
 * @param {Object, String} currently only functions are accepted, not function names
 */
// TODO make this suck less or remove it
jQuery.depends = function(func) {
	if ($.isFunction(func)) return jQuery;

	//console.error('Repos customization error. This plugin\'s dependencies are not met.');
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
	if (!this.repos_webappRoot) return '/repos-web/'; // best guess
	return this.repos_webappRoot;
};

/**
 * Static accessor for getWebapp, the application root with trailing slash
 */
Repos.url = Repos.getWebapp();

/**
 * @return the value of a page metadata field, or false if not existing.
 */
Repos.getMeta = function(id) {
	var m = document.getElementsByName('repos-'+id);
	if (m.length==0) return false;
	return m[0].getAttribute('content');
};

Repos.getTarget = function(context) {
	// functionality from the proplist plugin
	if (typeof context != 'undefined' && $(context).attr('title')) return $(context).attr('title');
	// target for this page
	return Repos.getMeta('target');
};

Repos.getService = function() {
	return Repos.getMeta('service');
};

/**
 * @return {String} root url, no trailing slash
 */
Repos.getRepository = function() {
	// no meta tag in index yet, xslt should and repository name in path made clickable
	if (Repos.isService('index/')) {
		var u = decodeURI(window.location.href);
		var t = Repos.getTarget();
		return u.substr(0, u.length - t.length);
	}
	// pages known to still lack repository meta: edit result page
	return Repos.getMeta('repository');
};

Repos.getBase = function() {
	return Repos.getMeta('base') || ''; // empty string allows returned optional value to be set in query string
};

/**
 * Return last changed revision of an item.
 * TODO should it be called getRevisionLastChanged like in backend code?
 * TODO make this work globally, currently it is from the proplist plugin and works on the details page
 * TODO context argument to specify the item if there are many on the same page
 * @return {int} the revision number when the item was last committed
 */
Repos.getRevision = function() {
	return parseInt($('#filedetails .revision:first').text(), 10);
};

Repos.isRevisionRequested = function() {
	return Repos.getRevisionRequested() !== null;
};

/**
 * Returns the revision requested by the client.
 * Can be any revision, always >=getRevision().
 * Boolean null if no revision requested.
 * This implementation does not support revisions such as HEAD or {date}.
 * TODO is this same as working/entry revision in svn info?
 */
Repos.getRevisionRequested = function() {
	var m = (/[?&]rev=(\d+)/).exec(window.location.search);
	return m && m[1];
};

/**
 * Compares current Repos target with a pattern
 * @param {String} selector The pattern to compare with
 * @param {Object} context Optional context to get current target for
 * @return true if current target matches the selector
 */
Repos.isTarget = function(selector,context) {
	// allow regexp as selector, matching target direcly
	if (typeof selector.test == 'function') return selector.test(Repos.getTarget(context));
	// not regexp, handle as Ant pattern
	var s = selector;
	// escape valid path characters
	s = s.replace(/([.+\^${}()\[\]\/])/g, '\\$1');
	// allow *a.txt notation for matching any path ending with a.txt
	s = s.replace(/^\*/,'**');
	// strict ant pattern rules
	s = s.replace(/\*\*/g,'.{0,}');
	s = s.replace(/\*/g,'[^//]{0,}');
	// match
	var r = new RegExp('^'+s+'$');
	var t = Repos.getTarget(context);
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
	if (u) return decodeURI(u); // should match rawurlencode in PHP
	return false;
};

// jQuery plugin to show messages
jQuery.fn.say = function(message) {
	$('.temporarymessage').remove();
	if (!message) return; // allow $(document).say(); to clear message
	
	if (typeof message == 'string') message = {text:message};
	var m = jQuery.extend({
		tag:'div',
		level:'note',
		text:'',
		title:false,
		id:false,
		temporary:true
	},message);
	
	var e = $('<'+m.tag+'/>').addClass(m.level).html(m.text);
	
	if (m.temporary) e.addClass('temporarymessage');
	
	if (m.title) e.attr('title',m.title);
	if (m.id) e.attr('id',m.id);
	
	// placement, highest priority last
	e.hide();
	// after first headline
	$('h1,h2,h3', this).eq(0).after(e);
	// inside form
	this.filter('form').find('fieldset').prepend(e.wrap('<p/>'));
	// after input
	this.filter('input').parent().append(e);
	// done
	e.show('slow');
};

/**
 * Multi-repo support.
 * Customize jQuery.ajax to transparently support 'base' parameter
 * (otherwise every plugin would need if-else for SVNPath/SVNParentPath)
 * It is still undecided how the GUI knows if server user SVNParentPath.
 */
/* Disabled to see how compatible we are, now there's ?rweb= so base should be implicit
(function supportMultiRepo() {
	var base = Repos.getBase();

	var _jQ_ajax = jQuery.ajax;
	jQuery.ajax = function(options) {
		if (base && options.url.match(/[?&]target=/) && !options.url.match(/[?&]base=/)) options.url += '&base=' + base;
		_jQ_ajax(options);
	};
})();
 */

	/*
	 Dynamic loading of scripts and css has been disabled,
	 because it was not reliable. Can be found in reposweb-1.1-B1.

	 Anyway, it seems like $(document).ready() in plugins work even
	 if they are loaded dynamically. So the only limitation is that
	 3rd party libs must be loaded in page head.

	 And how about $(document).load()?
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
