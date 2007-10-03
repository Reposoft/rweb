/**
 * Repos shared script logic (c) 2006 Staffan Olsson www.repos.se
 * @version $Id$
 */
var Repos = {};

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
	if (!this.repos_webappRoot) return '/repos/'; // best guess
	return this.repos_webappRoot;
};

/**
 * Static accessor for getWebapp, the application root with trailing slash
 */
Repos.url = Repos.getWebapp();

	/*
	 Dynamic loading of scripts and css has been disabled,
	 because it was not reliable. Can be found in reposweb-1.1-B1.
	 
	 Anyway, it seems like $(document).ready() in plugins work even
	 if they are loaded dynamically. So the only limitation is that
	 3rd party libs must be loaded in page head.
	 
	 And how about $().load()?
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

	// ------------ exception handling ------------
	
	/**
	 * Called in try...catch to do Error and Exception handling
	 * @param error String error message or Exception 
	 */
	Repos.reportError = function(error) {
		var strError = Repos._errorToString(error);
		var id = Repos.generateId();
		// send to errorlog
		Repos._storeError(strError, id);
		// show to user
		var msg = "Repos has run into a script error:\n" + strError + 
			  "\n\nThe details of this error have been logged so we can fix the issue. " +
			  "\nFeel free to contact support@repos.se about this error, ID \""+id+"\"." +
			  "\n\nBecause of the error, this page may not function properly.";
		Repos._alertError(msg);
	};
	
	/**
	 * Takes an error of any type and converts to a message String.
	 */
	Repos._errorToString = function(error) {
		if (typeof(error)=='Error') {
			return Repos._exceptionToString(error);
		}
		return ''+error;
	};
	
	/**
	 * Converts a caught exception to an error message.
	 */
	Repos._exceptionToString = function(exceptionInstance) {
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
	};
	
	/**
	 * Sends an error report to the server, if possible.
	 */
	Repos._storeError = function(error, id) {
		var logurl = "/repos/errorlog/";
		var info = Repos._getBrowserInfo();
		info += '&id=' + id + '&message=' + error;
		// NOT MAINTANED, test Repos.url+errorlog/ and convert to jQuery ajax 
		if (typeof(Ajax) != 'undefined') {
			var report = new Ajax.Request(logurl, {method: 'post', parameters: info});
		} else {
			window.status = error; // Find out a way to send an error report anyway	
			return;
		}
	};
	
	/**
	 * Shows the error to the user, without requiring attention.
	 * @deprecated use Repos.error
	 */
	Repos._alertError = function(msg) {
		Repos._log(Repos.loglevel.error, msg);
	};
	
	/**
	 * collect debug info about the user's environment
	 * @return as query string
	 */
	Repos._getBrowserInfo = function() {
		var query,ref,page,date;
		page=window.location.href; // assuming that script errors occur in tools
		ref=window.referrer;
		query = 'url='+escape(page)+'&ref='+escape(ref)+'&os='+escape(navigator.userAgent)+'&browsername='+escape(navigator.appName)
			+'&browserversion='+escape(navigator.appVersion)+'&lang='+escape(navigator.language)+'&syslang='+escape(navigator.systemLanguage);
		return query;
	};
	
	/**
	 * Generate a random character sequence of length 8
	 */
	Repos._generateId = function() {
		var chars = "ABCDEFGHIJKLMNOPQRSTUVWXTZ";
		var string_length = 8;
		var randomstring = '';
		var rnum;
		for (var i=0; i<string_length; i++) {
			rnum = Math.floor(Math.random() * chars.length);
			randomstring += chars.charAt(rnum);
		}
		return randomstring;
	};

// ------------ logging ------------
// firebug dummy is added to head.js, so we could use console directly from everywhere

Repos.loglevel = {
	info: 3,
	warn: 4,
	error: 5
};

Repos.info = function(message) {
	Repos._log(Repos.loglevel.info, message);
};

Repos.warn = function(message) {
	Repos._log(Repos.loglevel.warn, message);
};

// show error message. Use Repos.reportError for exceptions.
Repos.error = function(message) {
	Repos.reportError(message);
};

Repos._log = function(level, msg) {
	if (typeof(console) != 'undefined') { // FireBug console
		console.log(msg);
	} else if (typeof(window.console) != 'undefined') { // Safari 'defaults write com.apple.Safari IncludeDebugMenu 1'
		window.console.log(msg);
	} else if (level < Repos.loglevel.error) {
		return;
	} else if (typeof(Components)!='undefined' && typeof(Component.utils)!='undefined') { // Firefox console
		Components.utils.reportError(msg);
	} else {
		window.status = "Due to a script error the page is not fully functional."; // Contact support@repos.se for info, error id: " + id;
	}
};
