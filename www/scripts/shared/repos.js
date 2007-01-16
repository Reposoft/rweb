/**
 * Repos shared script logic (c) Staffan Olsson http://www.repos.se
 * Static functions, loaded after prepare and jquery.
 * @version $Id$
 */
var Repos = {

	// -------------- plugin setup --------------
	
	/**
	 * Adds a javascript to the current page and evaluates it (asynchronously).
	 * @param src script url from repos root, not starting with slash
	 * @param loadEventHandler callback function for when the script has been evaluated
	 * @return the script element that was appended
	 */
	addScript: function(src, loadEventHandler) {
		var s = document.createElement('script');
		if (loadEventHandler) s.onload = loadEventHandler;
		s.type = "text/javascript";
		s.src = Repos.getWebapp() + src;
		document.getElementsByTagName('head')[0].appendChild(s);
		return s;
	},

	/**
	 * Adds a stylesheet to the current page.
	 * @param src css url from repos root, not starting with slash
	 * @return the link element that was appended
	 * @todo is the appended css accepted by IE6?
	 */
	addCss: function(src) {
		var s = document.createElement('link');
		s.type = "text/css";
		s.rel = "stylesheet";
		s.href = Repos.getWebapp() + src;
		document.getElementsByTagName('head')[0].appendChild(s);
		return s;
	},
	
	/**
	 * Calculates webapp root based on the include path of this script (repos.js or head.js)
	 * @return String webapp root url with trailing slash
	 */
	getWebapp: function() {
		var tags = document.getElementsByTagName("head")[0].childNodes;
		var me = /scripts\/head\.js(\??.*)$|scripts\/shared\/repos\.js$/;
		
		for (i = 0; i < tags.length; i++) {
			var t = tags[i];
			if (!t.tagName) continue;
			var n = t.tagName.toLowerCase();
			if (n == 'script' && t.src && t.src.match(me)) // located head.js, save path for future use
				this.repos_webappRoot = t.src.replace(me, '');
		}
		if (!this.repos_webappRoot) return '/repos/'; // best guess
		return this.repos_webappRoot;
	},

	// ------------ exception handling ------------
	
	/**
	 * Allows common error reporting routines, and logging errors to server.
	 * @param error String error message or Exception 
	 */
	reportError: function(error) {
		var error = Repos._errorToString(error);
		var id = Repos.generateId();
		// send to errorlog
		Repos._logError(error, id);
		// show to user
		var msg = "Repos has run into a script error:\n" + error + 
			  "\n\nThe details of this error have been logged so we can fix the issue. " +
			  "\nFeel free to contact support@repos.se about this error, ID \""+id+"\"." +
			  "\n\nBecause of the error, this page may not function properly.";
		Repos._alertError(msg);
	},
	
	/**
	 * Takes an error of any type and converts to a message String.
	 */
	_errorToString: function(error) {
		if (typeof(error)=='Error') {
			return Repos._exceptionToString(error);
		}
		return ''+error;
	},
	
	/**
	 * Converts a caught exception to an error message.
	 */
	_exceptionToString: function(exceptionInstance) {
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
		return msg;
	},
	
	/**
	 * Sends an error report to the server, if possible.
	 */
	_logError: function(error, id) {
		var logurl = "/repos/errorlog/";
		var info = Repos._getBrowserInfo();
		info += '&id=' + id + '&message=' + error;
		if (typeof(Ajax) != 'undefined') {
			var report = new Ajax.Request(logurl, {method: 'post', parameters: info});
		} else {
			window.status = error; // Find out a way to send an error report anyway	
			return;
		}
	},
	
	/**
	 * Shows the error to the user, without requiring attention.
	 */
	_alertError: function(msg) {
		if (typeof(console) != 'undefined') { // FireBug console
			console.log(msg);
		} else if (typeof(window.console) != 'undefined') { // Safari 'defaults write com.apple.Safari IncludeDebugMenu 1'
			window.console.log(msg);
		} else if (typeof(Components)!='undefined' && typeof(Component.utils)!='undefined') { // Firefox console
			Components.utils.reportError(msg);
		} else { // don't throw exceptions because it disturbs the user, and repos works without javascript too
			window.status = "Due to a script error the page is not fully functional. Contact support@repos.se for info, error id: " + id;
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
	_generateId: function() {
		var chars = "ABCDEFGHIJKLMNOPQRSTUVWXTZ";
		var string_length = 8;
		var randomstring = '';
		for (var i=0; i<string_length; i++) {
			var rnum = Math.floor(Math.random() * chars.length);
			randomstring += chars.charAt(rnum);
		}
		return randomstring;
	}
	
}
