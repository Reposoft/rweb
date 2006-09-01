/**
 * Repos common script logic (c) Staffan Olssn http://www.repos.se
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
	}
};

if (reposScriptSetup == undefined) {
	alert('Script error. ReposScriptSetup must be loaded before the Repos class');	
}
// import the common plugins
for (i=0; i<reposScriptSetup.commonPlugins.length; i++) {
	Repos.require(reposScriptSetup.commonPlugins[i]);
}
