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
 * Repos common script logic (c) Staffan Olsson http://www.repos.se
 * Mutually dependent to ../head.js that imports the Prototype library. 
 * This is a static class, accessible anywhere using Repos.[method]
 * @version $Id$
 */
var Repos = {
	version: '$Rev$',

	// ------------ script initialization ------------
	
	initialize: function() {
		// settings
		this.dontCachePlugins = new Date().valueOf(); // for development, set to false for production
		
		// common page elements
		this.pageLoaded = false;
		this.documentBody = null;
		this.documentHead = document.getElementsByTagName("head")[0];
		this.defaultNamespace = "http://www.w3.org/1999/xhtml";
		
		// do the mandatory imports
		// these scripts don't need to be required by any other scripts
		this.path = this._getPath();
		this.require("lib/scriptaculous/prototype.js");
	},
	
	/**
	 * Must be called when page has loaded (body onload). All custom initialization is done after page has loaded.
	 */
	handlePageLoaded: function() {
		this.pageLoaded = true;
		this.documentBody = document.getElementsByTagName("body")[0];
		setTimeout(Repos.decoratePage, 500);
	},
	
	/**
	 * Add behaviours to the page after it has loaded.
	 */
	decoratePage: function() {
		
	},
	
	isPageLoaded: function() {
		return this.pageLoaded;
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
		alert('Exception: ' + exceptionInstance);
	},
	
	/**
	 * Allow direct error reporting from plugin code
	 */
	reportError: function(errorMessage) {
		alert('Error: ' + errorMessage);
	},
	
	// ------------ dependency management ------------
	
	/**
	 * Import a library so that it is immediately available to the calling script.
	 * Adds a library to the DOM and makes sure it is executed.
	 * @param url relative to this script (head.js) or absolute url from server root starting with '/'
	 */
	require: function(scriptUrl) {
		if (scriptUrl.indexOf('/')!=0) {
			scriptUrl = this.path + scriptUrl;	
		}
		var s = this.createElement("script");
		s.type = "text/javascript";
		s.src = scriptUrl;
		this.documentHead.appendChild(s);
	},
	
	/**
	 * Import a plugin given a name
	 * Script will be looked for in 'plugins/pluginName/pluginname.js'
	 */
	requirePlugin: function(pluginName) {
		if (nocache) {
			scriptUrl += '?'+nocache;	
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
	 * @see http://www.dotvoid.com/view.php?id=43
	 */
	addBefore: function(aspectFunction, object, objectFunction)
	{
	  var fType = typeof(objectFunction);
	
	  if (typeof(aspectFunction) != 'function')
		throw(InvalidAspectFunction);
	
	
		var oldFunctoin = obj.prototype[fName];
		if (!oldFunction)
		  throw InvalidMethod;
	
		obj.prototype[oldFunction] = function() {
			var replacement = aspectFunction.apply(this, arguments);
			if (replacement) {
				return replacement;	
			}
		  	return oldFunction.apply(this, arguments);
		}
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
	emptyFunction: function() {}
};

// call constructor for the static class
Repos.initialize();
// overwriting any existing event handler, from now on taking care of all window.onload
window.onload = Repos.handlePageLoaded();
