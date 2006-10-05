/**
 * Repos shared script logic (c) Staffan Olsson http://www.repos.se
 * Static functions, loaded after prepare and jquery.
 * @version $Id$
 *
 * reportError(error) - handles any error message or exception
 */
var Repos = {

	// -------------- plugin setup --------------
	
	addScript: function(src) {
		var s = document.createElement('script');
		s.type = "text/javascript";
		s.src = src;
		document.getElementsByTagName('head')[0].appendChild(s);
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
