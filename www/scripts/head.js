// $Id$
// Copyright (c) 2006 Staffan Olsson (http://www.repos.se)
// repos.se client side scripting platform

/*
This script loader should be refactored:
There will be one static class Repos (placed in this file).
It does not depend on any other scripts to be loaded in the page.


* lib: included by Repos as needed, not referenced directly from scripts
* plugins: modify the behaviour of a loaded page. Imported after page has loaded.

Standard libraries:
Prototype 1.5.0 rc1, some classes including Ajax and Selector.findChildElements
AOP, decorate Event.observe with special handling of 
	+ decorate document.createElement with createElementNS for xhtml+xml docs
XSLT, for the transform function

Repos.addBehaviour: http://www.ccs.neu.edu/home/dherman/javascript/behavior/
Repos.Before: http://www.dotvoid.com/view.php?id=43

For now:
Scripts can refer to the Repos class, but not ReposScriptSetup


*/

// This is a complement to prototype.js, 
// Scripts that use this library can be developed for prototype.js

/**
Pattern for calling plugins:
1. Do includes "Repos.require.."
2. Create the plugin class, with initializer
3. Use Prototype Event.observe to create a window.onload event that runs the initializer.
4. If any other plugins are expected after the initializer is called, do Repos.requirePlugin in the class.
When (1) comes before (3), Repos will load the dependencies before the initializer is run, which is good.

Plugins may use Repos.require to require libs, or Repos.requirePlugin to import other plugins.
Theme settings may only use Repos.requirePlugin.

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
var Repos = {
	version: '$Rev$',
	// ------------ script initialization ------------
	
	initialize: function() {
		// note that these are read-only fields. The Repos object is static and does not have state.
		
		// settings
		this.themeSettings = 'style/settings.js'; // relative to each theme's root
		this.allowCachePlugins = false; // set to true in production to allow browsers to cache plugins
		
		// common page elements
		//this.pageLoaded = false;
		this.documentHead = document.getElementsByTagName("head")[0];
		this.defaultNamespace = this._getNamespace();
		
		// overwriting any existing event handler, from now on taking care of all window.onload
		window.onload = Repos._handlePageLoaded;
		
		// do the mandatory imports
		// these scripts don't need to be required by any other scripts
		this.path = this._getPath();
		
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
		_repos_pageLoaded = true;
		// check or flow errors
		if (_repos_loading) Repos.reportError("Script logic error. Queue processing started before page load completed.");
		if (typeof(Prototype) == 'undefined') {
			Repos.reportError("Prototype library not loaded. All scripts deactivated.");
			return;
		}
		// add custom handling to Prototype Event.observe from now on
		try {
			Repos.addBefore(Repos._beforeObserve, Event, 'observe');
		} catch (err) {
			Repos.handleException(err + " Can not add custom window onload handling.");	
		}
		Repos._setUpXhtmlXmlCompatibility();
		Repos._loadThemeSettings();
		setTimeout(Repos._activateLoadqueue, 500);  // set the initial delay in milliseconds here
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
		var t = Repos.getTheme();
		// relative paths with ../ not handled yet. Note that the absolute url will not work offline.
		Repos.require('/repos/' + t + this.themeSettings);	
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
	 * @return the path of this script file, from the page's script tag, for use in relative include urls
	 */
	_getPath: function() {
		var scripts = document.getElementsByTagName("script");
		for (i=0; i<scripts.length; i++) {
			if (scripts[i].src && scripts[i].src.match(/head\.js(\?.*)?$/)) {
				return scripts[i].src.replace(/head\.js(\?.*)?$/,'');
			}
		}
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
	 * Prototype and such livraries are not tested with application/xhtml+xml and text/xml pages, so some customization is needed
	 */
	_setUpXhtmlXmlCompatibility: function() {
		// only needed if this is an XML page
		if (Repos.defaultNamespace) {
			// document.body in firefox
			if (document.body == undefined) {
				document.body = document.getElementsByTagName("body")[0];	
			}
			// document.createElement must be replaced with document.createElementNS (except in IE)
			Repos.addBefore(Repos.createElement, document, 'createElement');
		}
	},
	
	// ------------ exception handling ------------
	
	/**
	 * Take care of a caught exception.
	 * Methods in this class do try/catch when calling imported code.
	 */
	handleException: function(exceptionInstance) {
		this.reportError("(Exception) " + exceptionInstance);
	},
	
	/**
	 * Allow direct error reporting from plugin code
	 */
	reportError: function(errorMessage) {
		var id = this.generateId();
		alert("The following internal script error has occured:\n" + errorMessage + 
			  "\n\nThe error details have been reported to the repos.se developers. " +
			  "\nFeel free to contact support@repos.se about this error, ID \""+id+"\"." +
			  "\n\nBecause of the error, this page may not function properly.");
	},
	
	/**
	 * Generate a random string of length 8
	 */
	generateId: function() {
		return 'nonRndID';
	},
	
	// ------------ dependency management ------------
	
	/**
	 * Import a library or css so that it is available to the calling script.
	 * Verifies that the URL is valid.
	 * Repos.require can be done during any phase of page load, but everything will queue up,
	 * and processing of the queue will start only when the page has loaded.
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
			alert(pluginName + "plugin already loaded. applying callback directly");
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
		for (lib in _repos_loadedlibs) {
			if (lib == resourceUrl) return true;
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
		setTimeout(Repos._loadNext, 500); // set the delay between loads here (note that each load is syncronous, so this row is reached after the previously loaded script has benn processed
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
			if (scriptUrl.indexOf('/')!=0) {
				scriptUrl = this.path + scriptUrl;	
			}
			scriptUrl += this.scriptUrlSuffix;
			var s = this.createElement("script");
			s.type = "text/javascript";
			s.src = scriptUrl;
			this.documentHead.appendChild(s);		
		} catch (err) {
			Repos.reportError("Error loading script " + scriptUrl+ ": " + err);
		}
		return true;
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
		for (f in object) { members = members + " " + f }
		if (object.prototype) {
			for (f in object.prototype) { members = members + " prototype." + f }
		}
		throw "The object does not have a function " + objectFunction + ". It has:" + members;
	},
	
	// ------------ Session settings ------------
	
	/**
	 * @return user account name for the current user
	 */
	getUsername: function() {
		throw "getUsername is not implemented yet";
	},
	
	/**
	 * @return path to current theme, excluding /style, for example '' or 'themes/simple/'
	 *	false if no theme should be loaded (meaning that no theme script setup will be required.
	 */
	getTheme: function() {
		return '';
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
	
	setCookie: function(key, value) {
		throw "setCookie is not implemented yet";
	},
	
	setPersistentCookie: function(key, value) {
		throw "setPersistentCookie is not implemented yet";
	},
	
	/**
	 * @return value of the cookie, string
	 */
	getCookie: function(key) {
		throw "getCookie is not implemented yet";
	},
	
	// ------------ GUI commonality ------------
	
	/**
	 * Create a popup window
	 * @param id element ID
	 * @param options, as in http://prototype-window.xilinus.com/ but without className
	 * @returns window object with the API from http://prototype-window.xilinus.com/ (but not nessecarily the same class)
	 */
	createWindow: function(optionsHash) {
		var o = $H({ }).merge(optionsHash);
		return new Dialog(null, o);
	},
	
	// ----- last line of the class -----
	_emptyFunction: function() {}
};

// call constructor for the static class
Repos.initialize();
