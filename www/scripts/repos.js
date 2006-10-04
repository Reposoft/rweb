// $Id$
// Copyright (c) 2006 Staffan Olsson (http://www.repos.se)
// repos.se client side scripting platform

/*
This is the development file for repos scripts. when ready, code should be imported to head.js

The code requires jquery.
*/

/**
 * Repos common script logic (c) Staffan Olsson http://www.repos.se
 * Mutually dependent to ../head.js that imports the Prototype library. 
 * This is a static class, accessible anywhere using Repos.[method]
 * @version $Id$
 */
var _repos_pageLoaded = false;
var _repos_loadedlibs = new Array();
var _repos_loadqueue = new Array();
var _repos_loading = false;
var _repos_pluginLoadCallback = new Array();
var _repos_retries = 50;
var Repos = {
	version: '$Rev$',
	// ------------ script initialization ------------
	
	initialize: function() {
		// note that these are read-only fields. The Repos object is static and does not have state.
		
		// settings
		this.themeSettings = 'settings.js'; // relative to each theme's root
		this.allowCachePlugins = false; // set to true in production to allow browsers to cache plugins
		
		// common page elements
		//this.pageLoaded = false;
		this.documentHead = document.getElementsByTagName("head")[0];
		this.defaultNamespace = this._getNamespace();
		
		// overwriting any existing event handler, from now on taking care of all window.onload
		window.onload = Repos._handlePageLoaded;
		
		// prepare for the mandatory imports
		this.path = this._handleExistingHeadTags();
		// disable caching if needed, TODO the suffix should probably be generated per session instead. caching is not a problem for development, only for releases.
		if (this.allowCachePlugins || (this.path.length > 5 && this.path.substr(0, 5) == 'file:')) {
			this.scriptUrlSuffix = '';
		} else {
			this.scriptUrlSuffix = '?' + new Date().valueOf();	
		}
		// Prototype is needed for _handlePageLoaded, so bypass the load queue
		this._loadScript("lib/scriptaculous/prototype.js");
	},
	
	/**
	 * Must be called when page has loaded (body onload). All custom initialization is done after page has loaded.
	 */
	_handlePageLoaded: function() {
		// check required dependencies
		if (typeof(Prototype) == 'undefined') {
			// in IE, after forced refresh, onload might come before lib is loaded
			if (_repos_retries-- > 0) {
				window.setTimeout('Repos._handlePageLoaded()', 100);
				return;
			}
			Repos.reportError("Prototype library not loaded. All scripts deactivated.");
			return;
		}
		// add custom handling to Prototype Event.observe from now on
		try {
			Repos.addBefore(Repos._beforeObserve, Event, 'observe');
		} catch (err) {
			Repos.handleException(err + " Can not add custom window onload handling.");	
		}
		Repos.showVersion();
		Repos._setUpXhtmlXmlCompatibility();
		Repos._loadThemeSettings();
		// check for flow errors
		if (_repos_loading) Repos.reportError("Script logic error. Queue processing started before page load completed.");
		// ok, page is loaded, go process the load queue
		_repos_pageLoaded = true;
		window.setTimeout('Repos._activateLoadqueue()', 500);  // set the initial delay in milliseconds here
	},
	
	/**
	 * Pages may have linked to some libs, like Prototype, statically already,
	 *  in which case they should not be loaded again.
	 * Pages may also have linked to stylesheets from the default theme,
	 *  which should be changed if the current user has a different theme.
	 * (Pages that always want the default theme should include the CSS after head.js)
	 * @return path of this script (head.js) so it can be used in other script includes
	 */
	_handleExistingHeadTags: function() {
		var tags = this.documentHead.childNodes;
		var me = /head\.js(\?.*)?$/;
		var path = '';
		var scriptUrls = new Array();
		var theme = Repos.getTheme();
		
		for (i = 0; i < tags.length; i++) {
			var t = tags[i];
			if (!t.tagName) continue;
			var n = t.tagName.toLowerCase();
			if (n == 'script' && t.src && t.src.match(me)) // located head.js, save path for future use
				path = t.src.replace(me, '');
			if (n == 'script' && t.src)
				scriptUrls.push(t.src)
			if (n == 'link' && t.href && t.type && t.type == 'text/css')
				Repos._handleExistingCss(t, theme);
		}
		
		if (path.length < 1) {
			throw "Error: This script (head.js) file should be included in <head>"; 
		}
		
		while (s = scriptUrls.shift()) {
			Repos._handleExistingScript(s, path);
		}
		
		return path;
	},
	
	/**
	 * @param src the url of the script
	 * @param headPath the relative url to the root of scripts that Repos will handle
	 *  in IE, where the relative url such as ../ is the path, src must begin with headPath
	 */
	_handleExistingScript: function(src, headPath) {
		_repos_loadedlibs.push(src.substr(headPath.length));
	},
	
	/**
	 * Modify a css link so it uses the current theme.
	 * If the href contains /repos/style/ it will be updated with the theme name
	 * @param linkTag the element, child of <head>, with an href attribute
	 * @param theme the current user's theme selection, for example 'themes/simple/'
	 */
	_handleExistingCss: function(linkTag, theme) {
		var themePath = new RegExp('/repos/style/'); // default theme
		if (!themePath.test(linkTag.href)) return;
		var newpath;
		if (theme===false) {
			// cookies don't work in firefox 1.5 in XML pages, so we use theme redirector
			newpath = '/repos/themes/any/?u=';
		} else {
			// on the other hand relative urls in CSS after redirect don't work in other browsers
			newpath = '/repos/' + theme + 'style/';
		}
		var n = linkTag.cloneNode(true);
		var href = linkTag.href.replace(themePath, newpath);
		n.href = href;
		this.documentHead.replaceChild(n, linkTag);
	},
	
	/**
	 * @return true if body.onlod has happened
	 *   note that even then, load queue might still be processing
	 */
	isPageLoaded: function() {
		return _repos_pageLoaded;
	},	
	
	/**
	 * Append the theme setup script to the load queue.
	 */
	_loadThemeSettings: function() {
		// the dynamic js inclusion works, but not the cookie in firefox XML docs
		// var t = Repos.getTheme();
		var t = 'themes/any/?u=';
		// relative paths with ../ not handled yet. Note that the absolute url will not work offline.
		Repos.require('/repos/' + t + Repos.themeSettings);	
	},
	
	/**
	 * Interrupts Event.observe to check that window.onload is not set after page has loaded.
	 */
	_beforeObserve: function(element, name, observer, useCapture) {
		if (!Repos.isPageLoaded) return;
		if (element != window) return;
		if (name != 'load') return;
		// page has loaded already, execute now instead
		Repos._addToLoadqueue(observer);
		return true; // don't invoke the original method
	},
	
	/**
	 * @return the namespace for this document, for createElementNS, or null if not required
	 */
	_getNamespace: function() {
		if (document.documentElement) {
			if (document.documentElement.namespaceURI) {
				return document.documentElement.namespaceURI;
			}
		}
		return null;
	},
	
	/**
	 * Prototype and such libraries are not tested with application/xhtml+xml and text/xml pages, so some customization is needed
	 */
	_setUpXhtmlXmlCompatibility: function() {
		// only needed if this is an XML page
		if (Repos.defaultNamespace) {
			// document.body in firefox
			if (document.body == undefined) {
				document.body = document.getElementsByTagName("body")[0];	
			}
			// document.createElement must be replaced with document.createElementNS (except in IE)
			try {
				Repos.addBefore(Repos.createElement, document, 'createElement');
			} catch (err) {
				Repos.handleException(err);	
			}
		}
		// override document.write, it is not compatible with this script in normal pages either
		try {
			if (document.write) {
				Repos.addBefore(Repos.documentWrite, document, 'write');
			} else {
				document.write = function(markup) { Repos.documentWrite(markup) };	
			}
		} catch (err) {
			Repos.handleException(err);	
		}
	},
	
	// ------------ exception handling ------------
	
	/**
	 * Take care of a caught exception.
	 * Methods in this class do try/catch when calling imported code.
	 */
	handleException: function(exceptionInstance) {
		// if stacktraces are supported, add the info from it
		var msg = '(Exception';
		if (exceptionInstance.fileName) {
			msg += ' at ' + exceptionInstance.fileName;
		}
		if (exceptionInstance.lineNumber) {
			msg += ' row ' + exceptionInstance.lineNumber;
		}
		if (exceptionInstance.message) {
			msg += ') ' + exceptionInstance.message;
		} else {
			msg += ') ' + exceptionInstance;
		}
		Repos.reportError(msg);
	},
	
	/**
	 * Allow direct error reporting from plugin code
	 */
	reportError: function(errorMessage) {
		var id = Repos.generateId();
		// send to errorlog
		var logurl = "/repos/errorlog/";
		var info = Repos._getBrowserInfo();
		info += '&id=' + id + '&message=' + errorMessage;
		if (typeof(Ajax) != 'undefined') {
			var report = new Ajax.Request(logurl, {method: 'post', parameters: info});
		} else {
			window.status = errorMessage; // Find out a way to send an error report anyway	
			return;
		}
		// show to user
		var msg = "Repos has run into a script error:\n" + errorMessage + 
			  "\n\nThe details of this error have been logged so we can fix the issue. " +
			  "\nFeel free to contact support@repos.se about this error, ID \""+id+"\"." +
			  "\n\nBecause of the error, this page may not function properly.";
		
		if (typeof(console) != 'undefined') { // FireBug console
			console.log(msg);
		} else if (window.console) { // Safari 'defaults write com.apple.Safari IncludeDebugMenu 1'
			window.console.log(msg);
		} else { // browser's default handling
			//throw(msg);
			window.status = "Due to a script error, the page is not fully functional. Contact support@repos.se for info, error id: " + id;
		}	
	},
	
	/**
	 * collect debug info about the user's environment
	 * @return as query string
	 */
	_getBrowserInfo: function() {
		var query,ref,page,date;
		page=window.location.href; // assuming that script errors occur in tools
		ref=window.referrer;
		query = 'url='+escape(page)+'&ref='+escape(ref)+'&os='+escape(navigator.userAgent)+'&browsername='+escape(navigator.appName)
			+'&browserversion='+escape(navigator.appVersion)+'&lang='+escape(navigator.language)+'&syslang='+escape(navigator.systemLanguage);
		return query;
	},
	
	/**
	 * Generate a random character sequence of length 8
	 */
	generateId: function() {
		var chars = "ABCDEFGHIJKLMNOPQRSTUVWXTZ";
		var string_length = 8;
		var randomstring = '';
		for (var i=0; i<string_length; i++) {
			var rnum = Math.floor(Math.random() * chars.length);
			randomstring += chars.charAt(rnum);
		}
		return randomstring;
	},
	
	// ------------ dependency management ------------
	
	/**
	 * Import a library or css so that it is available to the calling script.
	 * Verifies that the URL is valid.
	 * Repos.require can be done during any phase of page load, but everything will queue up,
	 * and processing of the queue will start only when the page has loaded.
	 *
	 * Limitation:
	 * If a script has a dependency, and that dependency also does Repos.require, and the script 
	 * needs one of those dependencies at load time, they will not have loaded yet.
	 * For example when details plugin includes scriptaculous.
	 *
	 * @param url relative to this script (head.js) or absolute url from server root starting with '/'
	 */
	require: function(scriptUrl) {
		if (Repos.verifyResourceUrl(scriptUrl)) {
			Repos._addToLoadqueue(scriptUrl);
		} else {
			Repos.reportError("The required resource URL is invalid: " + scriptUrl);	
		}
		if (Repos.isPageLoaded()) {
			Repos._activateLoadqueue();
		}
	},
	
	// TODO requireLib
	
	/**
	 * Import a plugin given a name
	 * Script will be looked for in 'plugins/pluginName/pluginname.js'
	 */
	requirePlugin: function(pluginName) {
		var scriptUrl = Repos._getPluginUrl(pluginName);
		Repos.require(scriptUrl);
	},
	
	/**
	 * @return plugin js file url relative to script directory
	 */
	_getPluginUrl: function(pluginName) {
		return "plugins/" + pluginName + "/" + pluginName + ".js"
	},
	
	/**
	 * Set up event handler for when a plugin has loaded.
	 * @param pluginName for example 'dateformat'
	 * @param the callback function taking zero parameters
	 */
	observePluginLoad: function(pluginName, callbackFunction) {
		// TODO verify that the plugin will be loaded
		
		// simple solution - first let all plugins load, then run all callback functions
		if (Repos.isPageLoaded() && Repos.isScriptResourceLoaded(Repos._getPluginUrl(pluginName))) {
			alert(pluginName + "plugin already loaded. applying callback directly"); // debug. remove the first time you see it appear.
			callbackFunction.apply();
		}
		_repos_pluginLoadCallback.push(callbackFunction);
	},
	
	/**
	 * Verify that the resource exists (because browsers fail silently if not)
	 */
	verifyResourceUrl: function(resourceUrl) {
		// TODO a regex for valid resource strings
		
		//var req = new Ajax.Request(resourceUrl, {asynchronous:false, method:'head'});
		// Son't want to use synchronous ajax, because it blocks everything.
		// maybe it's better to allow the settings file to verify after all scripts have loaded
		return true;
	},
	
	isScriptResourceLoaded: function(resourceUrl) {
		for (i = 0; i < _repos_loadedlibs.length; i++) {
			if (_repos_loadedlibs[i] == resourceUrl) return true;
		}
		return false;
	},
	
	/**
	 * Load a function or a script into the page.
	 * This can be done during any phase of the page load.
	 * This function does not activate the load queue. That is done in Repos.require and Repos._handlePageLoaded.
	 */
	_addToLoadqueue: function(functionOrScript) {
		var t = typeof(functionOrScript);
		if (t == 'function' || t == 'string') {
			_repos_loadqueue.push(functionOrScript);
		} else {
			Repos.reportError("Can not add object of type " + t + " to load queue");	
		}
	},
	
	/**
	 * Start processing of the load queue. If it is started already, do nothing.
	 * Calls _loadNext directly, so that there is no delay.
	 */
	_activateLoadqueue: function() {
		if (!Repos.isPageLoaded()) Repos.reportError("Script logic error. Should not activate load queue when page is still loading");
		if (!_repos_loading) {
			_repos_loading = true;
			Repos._loadNext();
		}
	},
	
	/**
	 * Gets the first script or function from the queue, and runs it.
	 * Then makes a timeout call to itself.
	 */
	// TODO who checks for duplicates in the load queue?
	_loadNext: function() {
		if (_repos_loadqueue.length == 0) {
			_repos_loading = false;
			return;
		}
		functionOrScript = _repos_loadqueue.shift();
		var t = typeof(functionOrScript);
		if (t == 'function') {
			functionOrScript.apply();			
		} else if (t == 'string') {
			var ok = Repos._loadScript(functionOrScript);
			if (ok) {
				Repos._handleScriptLoaded(functionOrScript);
			} else {
				// script was not loaded. Run the next in queue immediately.
				Repos._loadNext();	
			}
		}
		window.setTimeout('Repos._loadNext()', 500); // set the delay between loads here (note that each load is syncronous (at least in most browsers), so this row is reached after the previously loaded script has been processed
	},
	
	/**
	 * Adds a script to the DOM.
	 * Saves the script url in the loadedlibs array.
	 * If isScriptResrouceLoaded already, it returns false.
	 * If load is successful, returns true.
	 */
	_loadScript: function(scriptUrl) {
		if (Repos.isScriptResourceLoaded(scriptUrl)) {
			return false;	
		}
		_repos_loadedlibs.push(scriptUrl);
		try {
			var s = Repos.createElement("script");
			s.type = "text/javascript";
			s.src = Repos._prepareScriptUrl(scriptUrl);
			Repos.documentHead.appendChild(s);		
		} catch (err) {
			Repos.reportError("Error loading script " + scriptUrl+ ": " + err);
		}
		return true;
	},
	
	/**
	 * Add prefix and suffix to script url
	 */
	_prepareScriptUrl: function(scriptUrl) {
		if (scriptUrl.indexOf('/')!=0) {
			scriptUrl = Repos.path + scriptUrl;	
		}
		// 
		if (scriptUrl.indexOf('?')>=0) {
			return scriptUrl + Repos.scriptUrlSuffix.replace(/\?/,'&');
		} else {
			return scriptUrl + Repos.scriptUrlSuffix;
		}
	},
	
	/**
	 * Since scripts are loaded asynchronously
	 */
	_handleScriptLoaded: function(scriptUrl) {
		// TODO implement a real event model
		if (Repos.isPageLoaded() && _repos_loadqueue.length == 0) { // this is a stupid solution because it may occur multiple times if there are delayed require calls. need an associative array for callbacks, and identify the loaded plugin here
		// simple solution - first let all plugins load, then run all callback functions
		while (_repos_pluginLoadCallback.length > 0) {
			var f = _repos_pluginLoadCallback.shift();
			try {
				f.apply();
			} catch (err) {
				Repos.reportError("Custom callback function failed for loaded script " + scriptUrl + ": " + err); 
			}
		}
		} // end stupid solution
	},
	
	// ------------ DOM manipulation ------------

	/**
	 * Creates a new DOM element
	 * A replacement for document.createElement in application/xhtml+xml and text/xml pages
	 * @param tagName name in XHTML namespace
	 * @param elementId id attribute value
	 * @returns the element reference
	 */
	createElement: function(tagname) {
		if (Repos.defaultNamespace && document.createElementNS) {
			return document.createElementNS(Repos.defaultNamespace, tagname);
		} else { // IE does not support createElementNS
			return document.createElement(tagname);	
		}
	},
	
	/**
	 * Custom document.write, because the original is not allowed in xml documents
	 */
	documentWrite: function(html) {
		Repos.reportError("document.write is not allowed. Detected attempt to write " + html);
		// abort the real call
		return true;
	},
	
	// ------------ AOP concepts ------------
	
	/**
	 * Adds a before advice to an existing function
	 * The advice is executed before the real function.
	 * If the advice returns anything, the real function will not be called.
	 * This is immensely useful for unit testing.
	 * @see http://www.dotvoid.com/view.php?id=43
	 */
	addBefore: function(aspectFunction, object, objectFunction)
	{
		var oType = typeof(object);
		// 'Event' is a function is Firefox and an object in IE
		if (typeof(object) != 'function' && oType != 'object')
			throw "No target object given. Can not create Before advice.";
	
		if (typeof(aspectFunction) != 'function')
			throw("The aspectFunction '" + aspectFunction + "' is not valid. Can not create Before advice");
	
		if (typeof(objectFunction) != 'string')
			throw("objectFunction for Before advice should be a string, was " + typeof(objectFunction));

		
		var oldFunction = object[objectFunction];
		if (oldFunction) {
			object[objectFunction] = function() {
				var replacement = aspectFunction.apply(this, arguments);
				if (replacement) {
					return replacement;	
				}
				return oldFunction.apply(this, arguments);
			}
			return;
		}
		if (object.prototype) {
			oldFunction = object.prototype[objectFunction];
			if (oldFunction) {
				object.prototype[objectFunction] = function() {
					var replacement = aspectFunction.apply(this, arguments);
					if (replacement) {
						return replacement;	
					}
					return oldFunction.apply(this, arguments);
				}
				return;
			}
		}

		var members = "";
		for (f in object) { members += " " + f; if(members.length>40){members+=' ...'; break;} }
		if (object.prototype) {
			for (f in object.prototype) { members += " prototype." + f; if(members.length>80){members+=' ...';break;} }
		}
		throw "The object does not have a function " + objectFunction + ". It has:" + members;
	},
	
	// ------------ Session settings ------------
	
	/**
	 * @return user account name for the current user, or false if not logged in
	 */
	getUsername: function() {
		var user = Repos.getCookie('username');
		return user;
	},
	
	/**
	 * @return path to current theme, excluding /style, for example '' or 'themes/simple/'
	 *	false if no theme should be loaded (meaning that no theme script setup will be required.
	 */
	getTheme: function() {
		if (typeof(document.cookie)=='undefined') return false;
		var user = Repos.getUsername();
		// settings are currently hard coded because no user preferences system is in operation
		if (!user||user=='test'||user=='annika'||user=='arvid'||user=='hanna') 
			return '';
		return 'themes/pe/';
	},
	
	/**
	 * @return two char language code, lowercase, for exmaple 'en', 'de' or 'sv'
	 */
	getLocale: function() {
		return 'en';
	},
	
	/* does anyone need this? getUserTimeZone: function() {
		throw "getUserTimeZone is not implemented yet";
	} */
	
	/**
	 * @return the offset in _minutes_ from UTC, _including_ daylight savings time when used.
	 * A positive value means later than UTC, meaning more to the east of the globe
	 */
	getUserUtcOffset: function() {
		throw "getUserUtcOffset is not implemented yet. date.getTimezoneOffset works well enough so far.";
	},
	
	// ------------ Cookie functions ------------
	
	/**
	 * setCookie(name, value, [days])
	 * @param days optional, if 0 or missing, the cookie expires when the browser is closed
	 */
	setCookie: function(name, value, days) {
		var c = name+"="+escape(value);
		if (days) c += "; expires="+Repos._getexpirydate(days);
		document.cookie=c;
	},
	
	/**
	 * Read a cookie value
 	 * @param name Name of the desired cookie.
     * Returns a string containing value of specified cookie,
 	 *   or null if cookie does not exist.
	 */
	getCookie: function(name) {
		if (typeof(document.cookie)=='undefined') {
			throw new Error("document.cookie has no properties");
			return null;
		}
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	},
	
	/**
	 * Clear the value of a cookie.
	 */
	deleteCookie: function(name) {
		Repos.setCookie(name,"",-1);	
	},
	
	_getexpirydate: function(days) {
		var UTCstring;
		Today = new Date();
		now=Date.parse(Today);
		Today.setTime(now+days*24*60*60*1000);
		UTCstring = Today.toUTCString();
		return UTCstring;
	},

	// ------------ GUI commonality ------------
	
	/**
	 * Create a popup window
	 * @param id element ID
	 * @param options, as in http://prototype-window.xilinus.com/ but without className
	 * @returns window object with the API from http://prototype-window.xilinus.com/ (but not nessecarily the same class)
	 * @deprecated use separate plugins, like jQuery textbox plugi
	 */
	createWindow: function(optionsHash) {
		var o = $H({ }).merge(optionsHash);
		return new Dialog(null, o);
	},
	
	// ----- marking screens -----
	_getReleaseVersion: function(versionText) {
		var rid = new ReposResourceId(versionText);
		return rid.getTextBefore() + rid.getRelease() + rid.getTextAfter();
	},
	
	_getResourceVersion: function(versionText) {
		var rid = new ReposResourceId(versionText);
		var release = rid.getRelease();
		if (rid.isTag) return rid.getTextBefore() + release + rid.getTextAfter();
		return rid.getTextBefore() + release + ' ' + rid.getRevision() + rid.getTextAfter();
	},
	
	showVersion: function() {
		try {
			var release = $('releaseversion');
			if (release) {
				release.innerHTML = Repos._getReleaseVersion(release.innerHTML);
				release.style.display = '';
			}
			var revision = $('resourceversion');
			if (revision) {
				revision.innerHTML = Repos._getResourceVersion(revision.innerHTML);
				revision.style.display = '';
			}
		} catch (err) {
			Repos.reportError(err);
		}
	},
	
	// ----- last line of the class -----
	_emptyFunction: function() {}
};

// call constructor for the static class
Repos.initialize();

function ReposResourceId(text) {
	this.text = text;
	this.getRelease = function() {
		if (/\/trunk\//.test(this.text)) return 'dev';
		var b = /\/branches\/[^\/\d]+(\d[^\/]+)/.exec(this.text);
		if (b) return b[1] + ' dev';
		var t = /\/tags\/[^\/\d]+(\d[\d\.]+)/.exec(this.text);
		if (t) {
			this.isTag = true;
			return t[1];
		}
		return '';
	}
	this.getRevision = function() {
		var rev = /Rev:\s(\d+)/.exec(this.text);
		if (rev) return rev[1];
		rev = /Id:\s\S+\s(\d+)/.exec(this.text);
		if (rev) return rev[1];
		return '';
	}
	this.getTextBefore = function() {
		return /(^[^\$]*)/.exec(this.text)[1];
	}
	this.getTextAfter = function() {
		return /([^\$]*$)/.exec(this.text)[1];
	}
}
