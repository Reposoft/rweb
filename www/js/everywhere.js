// ------ Elementary page functions ----

// return url of main contents
function getMainUrl() {
	return window.location.href;
}

// return referrer of main contents
function getMainReferrer() {
	return window.referrer;
}

// ----- Client-server communication scripts -----

// collect debug info about the user's environment
function getBrowserInfo() {
	var query,ref,page,date;
	page=getMainUrl(); // assuming that script errors occur in tools
	ref=getMainReferrer();
	query = 'page='+escape(page)+'&ref='+escape(ref)+'&os='+escape(navigator.userAgent)+'&browsername='+escape(navigator.appName)
		+'&browserversion='+escape(navigator.appVersion)+'&lang='+escape(navigator.language)+'&syslang='+escape(navigator.systemLanguage);
	if(navigator.appVersion.substring(0,1) > '3') {
		date = new Date();
		query = query + '&screenx='+screen.width+'&screeny='+screen.height+'&timezone='+date.getTimezoneOffset();
	}
	return query;
}

// Report script error to server
function reportScriptError(message) {
	// collect data
	var query = getBrowserInfo();
	query = query + '&message=' + message;
	// done
	reportBack(query);
}

// send request to server without bothering user
function reportBack(queryString) {
	try {
		pic1 = new Image(1,1); 
		pic1.src = '/reportError.jwa?report-method=image&'+queryString;
	} catch (e) {}
}