/**
 * Repos common script logic (c) Staffan Olsson http://www.repos.se
 * Mutually dependent to ../head.js that imports the Prototype library. 
 * This is a static class, accessible anywhere using Repos.[method]
 * @version $Id$
 */
var Repos = {
	/**
	 * Adds a library to the DOM and makes sure it is executed.
	 * @scriptUrl starting with '/' means absolute, no starting '/' means relative to head.js location
	 */
	require: function(scriptUrl) {
		reposScriptSetup.require(scriptUrl);
	},
	/**
	 * Creates a new DOM element
	 * Replace document.createElement in application/xhtml+xml pages
	 * @param tagName name in XHTML namespace
	 * @param elementId id attribute value
	 * @returns the element reference
	 */
	create: function(tagName, elementId) {
		var e = reposScriptSetup.createElement(tagName);
		if (elementId) {
			e.id = elementId;
		}
		return e;
	},
	/**
	  * Create a popup window
	  * @param id element ID
	  * @param options, as in http://prototype-window.xilinus.com/ but without className
	  * @returns window object with the API from http://prototype-window.xilinus.com/ (but not nessecarily the same class)
	  */
	createWindow: function(id, options) {
		var o = $H({className: "alphacube"}).merge(options);
		return new Window(id, o);
	},
	
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
	}
	
};

if (reposScriptSetup == undefined) {
	alert('Script error. ReposScriptSetup must be loaded before the Repos class');	
}
// import the common plugins
for (i=0; i<reposScriptSetup.commonPlugins.length; i++) {
	Repos.require(reposScriptSetup.commonPlugins[i]);
}
