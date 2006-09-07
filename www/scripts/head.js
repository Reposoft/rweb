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
var Repos = {
	version: '$Rev$',
	// ------------ script initialization ------------
	
	initialize: function() {
		// note that these are read-only fields. The Repos object is static and does not have state.
		
		// settings
		this.dontCachePlugins = new Date().valueOf(); // for development, set to false for production
		
		// common page elements
		//this.pageLoaded = false;
		this.documentHead = document.getElementsByTagName("head")[0];
		this.defaultNamespace = "http://www.w3.org/1999/xhtml";
		
		// overwriting any existing event handler, from now on taking care of all window.onload
		window.onload = Repos._handlePageLoaded;
		
		// do the mandatory imports
		// these scripts don't need to be required by any other scripts
		this.path = this._getPath();
		this.require("lib/scriptaculous/prototype.js");
	},
	
	/**
	 * Must be called when page has loaded (body onload). All custom initialization is done after page has loaded.
	 */
	_handlePageLoaded: function() {
		_repos_pageLoaded = true;
		// check that Prototype is loaded
		if (Prototype == undefined) {
			reportError("Prototype library not loaded");	
		}
		// add custom handling to Prototype Event.observe from now on
		try {
			Repos.addBefore(Repos._beforeObserve, Event, 'observe');
		} catch (err) {
			Repos.handleException(err + " Can not add custom window onload handling.");	
		}
	},
	
	isPageLoaded: function() {
		return _repos_pageLoaded;
	},
	
	/**
	 * Interrupts Event.observe to check that window.onload is not set after page has loaded.
	 */
	_beforeObserve: function(element, name, observer, useCapture) {
		if (!Repos.isPageLoaded) return;
		if (element != window) return;
		if (name != 'load') return;
		_repos_loadqueue.push(observer); // page has loaded already, execute now instead
		return true;
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
	 * @param url relative to this script (head.js) or absolute url from server root starting with '/'
	 */
	require: function(scriptUrl) {
		if (!Repos.isPageLoaded()) {
			Repos._loadScript(scriptUrl);		
		} else {
			if (Repos.verifyResourceUrl(scriptUrl)) {
				Repos._addToLoadqueue(scriptUrl);
			} else {
				Repos.handleError("The required resource URL is invalid: " + scriptUrl);	
			}
		}
	},
	
	/**
	 * Import a plugin given a name
	 * Script will be looked for in 'plugins/pluginName/pluginname.js'
	 */
	requirePlugin: function(pluginName) {
		var scriptUrl = "plugins/" + pluginName + "/" + pluginName + ".js";
		if (this.dontCachePlugins) {
			scriptUrl += '?'+this.dontCachePlugins;	
		}
		Repos.require(scriptUrl);
	},
	
	/**
	 * Verify, using AJAX to get HTTP headers, that the resource exists (because browsers fail silently if not)
	 */
	verifyResourceUrl: function(resourceUrl) {
		return true;
		// todo check that url exists
	},
	
	/**
	 * Adds a script to the DOM
	 */
	_loadScript: function(scriptUrl) {
		if (Repos.isScriptResourceLoaded(scriptUrl)) { // TODO now we wait another time interval before attempting next in queue
			return;	
		}
		_repos_loadedlibs.push(scriptUrl);
		try {
			if (scriptUrl.indexOf('/')!=0) {
				scriptUrl = this.path + scriptUrl;	
			}
			var s = this.createElement("script");
			s.type = "text/javascript";
			s.src = scriptUrl;
			this.documentHead.appendChild(s);		
		} catch (err) {
			Repos.reportError("Error loading script " + scriptUrl+ ": " + err);
		}
	},
	
	isScriptResourceLoaded: function(resourceUrl) {
		for (lib in _repos_loadedlibs) {
			if (lib == resourceUrl) return true;
		}
		return false;
	},
	
	/**
	 * Add behaviours to the page after it has loaded.
	 */
	_activateLoadqueue: function() {
		if (!_repos_loading) {
			_repos_loading = true;
			setTimeout(Repos._loadNext, 500);
		}
	},
	
	// who validates that a script exists
	// who checks for duplocates in the load queue
	
	_loadNext: function() {
		if (_repos_loadqueue.length == 0) {
			Repos.reportError("Load queue is empty but script is still loading.");
		}
		functionOrScript = _repos_loadqueue.shift();
		var t = typeof(functionOrScript);
		if (t == 'function') {
			functionOrScript.apply();			
		} else if (t == 'string') {
			Repos._loadScript(functionOrScript);
		}
		if (_repos_loadqueue.length == 0) {
			_repos_loading = false;	
		} else {
			setTimeout(Repos._loadNext, 500);	
		}
	},
	
	_addToLoadqueue: function(functionOrScript) {
		var t = typeof(functionOrScript);
		if (t == 'function' || t == 'string') {
			_repos_loadqueue.push(functionOrScript);
			Repos._activateLoadqueue();
		} else {
			Repos.reportError("Can not add object of type " + t + " to load queue");	
		}
	},
	
	// ------------ DOM manipulation ------------

	/**
	 * Creates a new DOM element
	 * A replacement for document.createElement in application/xhtml+xml pages
	 * @param tagName name in XHTML namespace
	 * @param elementId id attribute value
	 * @returns the element reference
	 */
	createElement: function(tagname) {
		if (document.createElementNS) {
			return document.createElementNS(this.defaultNamespace, tagname);
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
