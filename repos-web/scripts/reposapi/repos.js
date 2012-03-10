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

// ------------ logging ------------
// Firebug dummy is added to head.js - use console directly

Repos.contentHandlers = [];

/**
 * Replaces $(document).ready since jQuery ready runs even if selector is empty.
 * @param {string} repos selector starting with :
 * @param {function()} fn for jQuery().ready
 * @deprecated jQuery $(document).ready recommended now that repos selectors are removed
 */
Repos.ready = function(selector, fn) {
	return $('html'+selector).size() && $(document).ready(fn);
};
/**
 * Shorthand for Repos.ready(':repos-target(t)',fn)
 * @param {string} t
 * @param {function()} fn
 */
Repos.target = function(t, fn) {
	//Repos.contentHandlers.push({service:null, target:t, handler:fn});
	return Repos.isTarget(t) && $(document).ready(fn);
};
/**
 * Shorthand for Repos.ready(':repos-service(s)',fn)
 * @param {string} s
 * @param {function()} fn
 */
Repos.service = function(s, fn) {
	//Repos.contentHandlers.push({service:s, target:null, handler:fn});
	return Repos.isService(s) && $(document).ready(fn);
};

/**
 * Registers content handler.
 * @param {string} service Match services, null for all
 * @param {string|RegExp} target Match targets, null for all
 * @param {function} fn Callback, content container given as thisArg
 */
Repos.content = function(service, target, fn) {
	Repos.contentHandlers.push({service:service, target:target, handler:fn});
	var t = function() {
		if (target && Repos.isTarget(target)) {
			fn.apply(document);
		}
	};
	if (service) {
		Repos.service(service, t);
	} else {
		t();
	}
};

/**
 * Invokes service and target handlers after page load.
 * 
 * Note that Repos.getTarget and Repos.getService have
 * not been updated to work with custom containers.
 * 
 * @param service {string} The service that is now loaded
 * @param target {string} The target that this service operates on
 * @param container {jQuery} The element that this service is loaded in,
 *  corresponding to document for full page services
 */
Repos.asyncService = function(service, target, container) {
	for (var i = 0; i < Repos.contentHandlers.length; i++) {
		var h = Repos.contentHandlers[i];
		if (!h.service || Repos.isService(h.service, container, service)) {
			if (!h.target || Repos.isTarget(h.target, container, target)) {
				console.debug('invoking handler', service, target, h);
				h.handler.apply(container);
			}
		}
	}
};

/**
 * Generic trigger on user interface parameter.
 * 
 * Act on parameter change in hash (jQuery BBQ hash style),
 * including param set when this function is called (typically on load).
 * 
 * TODO support reuse of namespace
 * 
 * @param {string} pname Parameter name in hash, query string style
 * @param {function(string)} callback Callback on change, given the parameter value as single argument
 * @param {string=} evns Specific namespace, null to get triggered on global hashchange
 */
Repos.onUiParam = function(pname, callback, evns) {
	if (typeof evns == 'undefined') {
		evns = ('' + Math.random()).substr(2); // need a namespace so initial trigger is not repeated
	}
	var evname = 'hashchange' + (evns ? '.' + evns : '');
	var pv = null;
	$(window).bind(evname, function(ev) {
		var v = ev.getState(pname);
		if (typeof v == 'undefined') v = null; // param not set
		if (pv !== v) {
			callback(v);
		}
		pv = v;
	});
	$(window).trigger(evname);
};

/**
 * Calculates webapp root based on the include path of this script (repos.js or head.js)
 * @return {string} Webapp root url with trailing slash
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
 * @return {string} root url, no trailing slash
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
	var m = (/[?&](rev|p)=(\d+)/).exec(window.location.search);
	return m && m[2];
};

/**
 * Compares current Repos target with a pattern
 * @param {string} selector The pattern to compare with
 * @param {Element=} context Optional context to get current target for
 * @param {string=} t Actual target to compare with, overries Repos.getTarget
 * @return true if current target matches the selector
 */
Repos.isTarget = function(selector,context,t) {
	if (typeof t == 'undefined') {
		t = Repos.getTarget(context);
		console.debug('isTarget', selector, this, 'got', t);
	}
	// allow regexp as selector, matching target directly
	if (typeof selector.test == 'function') return selector.test(t);
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
	var is = r.test(t);
	return is;
};

/**
 * Compares current Repos service with a pattern
 * @param {string} selector The service name, a relative path, to compare with
 * @param {Element=} context
 * @param {string=} s Actaul value, overrides Repos.getService()
 */
Repos.isService = function(selector,context,s) {
	// a bit more flexibility
	if (Repos.getMeta('serv') !== 'embed') {
		// use "details/" for the full details page, "open/" for all view modes
		if (selector == 'details/') selector = 'open/'
	}
	if (typeof s == 'undefined') s = Repos.getService();
	if (selector == 'view/') selector = 'open/file/';
	if (selector == 'open/view/') selector = 'open/file';
	// check with page meta
	return (s == selector);
};

/**
 * Reads the username cookie
 * @return {string} false if user not authenticated through repos, else username
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
		select:'reposmessage',
		tag:'div',
		level:'note',
		text:'',
		title:false,
		id:false,
		temporary:true
	},message);
	
	var e = $('.' + m.select);
	var existing = e.size() > 0;
	if (existing) {
		m.temporary = message.temporary || false;
	} else {
		e = $('<'+m.tag+'/>');
	}
	e.hide().addClass(m.level);
	
	if (m.temporary) e.addClass('temporarymessage');
	
	if (m.title) e.attr('title',m.title);
	if (m.id) e.attr('id',m.id);
	
	e.html(m.text);
	// placement, highest priority last
	if (!existing) {
		// after first headline
		$('h1,h2,h3', this).eq(0).after(e);
		// inside form
		this.filter('form').find('fieldset').prepend(e.wrap('<p/>'));
		// after input
		this.filter('input').parent().append(e);
	}
	e.show('fast');
};

/**
 * jQuery plugin to make element expandable/collapsable.
 * Elements start collapsed.
 * Only some element structures are supported.
 * To toggle, toggle class collapsed/expanded.
 * @param {string} [state] State to switch to, "collapsed" or "expanded"
 */
jQuery.fn.reposCollapsable = function(state) {
	var support = { // matches css
			'dl': '> lh',
			'div': '> h2:first-child, h3:first-child'
		};
	if (typeof state == 'undefined') state = 'collapsed';
	var enabler = function(container, clickElem) {
		return function() {
			container.addClass('collapsable').addClass(state);
			clickElem.css('cursor', 'pointer').click(function() {
				container.toggleClass('collapsed').toggleClass('expanded');
			});
		}
	};
	return this.each(function() {
		var $this = $(this);
		for (var t in support) {
			if ($this.is(t)) {
				var c = $(support[t], $this);
				if ($this.is('.collapsable')) {
					if (!$this.is(state)) {
						c.trigger('click');
					}
				} else {
					enabler($this, c)();
				}
			}
		}
	});
};
