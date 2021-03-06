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
Repos.targetOverride = false; // temporary solution for all the getTarget calls without context

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
	if (service) {
		var targetmatch = target && Repos.isTarget(target); // check immediately, along with isSerivice, not after load 
		Repos.service(service, function() {
			if (!target || targetmatch) fn.apply(document);
		});
	} else if (target) {
		Repos.target(target, fn);
	} else {
		$(document).ready(fn);
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
	container.data({reposService: service, reposTarget: target});
	Repos.targetOverride = target; // TODO get rid of this override concept, make plugins use getTarget(context)
	for (var i = 0; i < Repos.contentHandlers.length; i++) {
		var h = Repos.contentHandlers[i];
		if (!h.service || Repos.isService(h.service, container, service)) {
			if (!h.target || Repos.isTarget(h.target, container, target)) {
				h.handler.apply(container);
			}
		}
	}
};
$(document).bind('repos-content-end-internal', function() {
	Repos.targetOverride = false;
});

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

/**
 * TODO support new style context target, see getService
 * @param {Element=} context Container for the target
 * @returns Target path in repository
 */
Repos.getTarget = function(context) {
	// functionality from the proplist plugin, the old context shortcut
	if (typeof context != 'undefined' && $(context).attr('title')) return $(context).attr('title');
	// new shortcut, until all calls send proper context
	if (typeof context == 'undefined' && Repos.targetOverride) return Repos.targetOverride;
	// target for this page
	var t = Repos.getMeta('target');
	if (t == '//') t = '/';
	return t;
};

/**
 * Uses meta value by default, although that storage method
 * might be deprecated with HTML5.
 * 
 * If a context is given the value from asyncService
 * will be returned. If there is no such value,
 * the meta value will be used if context=document.
 * 
 * @param {Element=} context Container for the service
 * @returns Service identifier, commonly with trailing slash but not with leading.
 */
Repos.getService = function(context) {
	if (typeof context != 'undefined') {
		var d = $(context).data();
		if (typeof d != 'undefined' && d.reposService) {
			return d.reposService;
		}
		if (!$(context).is(document)) {
			console.warn('No service set for requested context', context);
			return null;
		}
	}
	return Repos.getMeta('service');
};

/**
 * @return {string} root url, no trailing slash
 */
Repos.getRepository = function() {
	// no meta tag in index yet, but base is a link
	if (Repos.isService('index/')) {
		var rootlink = $('#base');
		if (rootlink.size()) return $('#base')[0].href.replace(/\/$/,'');
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
 * Reads operative revision if there is both p and r, but of course the getTarget is for peg.
 * TODO is this same as working/entry revision in svn info?
 */
Repos.getRevisionRequested = function() {
	// result is safely cacheable as long as we get it from location.search
	var c = Repos.getRevisionRequested; 
	if (c.hasOwnProperty('_peg')) {
		return c._rev || c._peg;
	}
	c._peg = null;
	var re = /[?&](rev|p|r)=(\d+)/g;
	var m;
	while ((m = re.exec(window.location.search)) !== null) {
		if (m[1] == 'r') {
			c._rev = m[2];
		} else {
			c._peg = m[2];
		}
	};
	return c._rev || c._peg;
};

/**
 * Compares current Repos target with a pattern
 * @param {string} selector The pattern to compare with
 * @param {Element=} context Optional context to get current target for
 * @param {string=} t Actual target to compare with, overries Repos.getTarget
 * @return true if current target matches the selector
 */
Repos.isTarget = function(selector,context,t) {
	if (typeof t == 'undefined') t = Repos.getTarget(context);
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
	if (typeof s == 'undefined') s = Repos.getService(context);
	// a bit more flexibility, although use of the embed mode is experimental and will probably not be maintained
	if (Repos.getMeta('serv') !== 'embed') {
		// use "details/" for the full details page, "open/" for all view modes
		if (selector == 'details/') selector = 'open/'
	}
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
					if (!$this.is('.' + state)) {
						c.trigger('click');
					}
				} else {
					enabler($this, c)();
				}
			}
		}
	});
};
