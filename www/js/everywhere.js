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
	page=getMainUrl();
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


// ----- Cookie handling -----

// getCookie(name) - reads a cookie. Returns null if not found
function getCookie(name) {
    var prefix = name + "=";
    var cookieStartIndex = document.cookie.indexOf(prefix);
    if (cookieStartIndex == -1) {
	return null;
    }
    var cookieEndIndex = document.cookie.indexOf(";", cookieStartIndex +
						 prefix.length);
    if (cookieEndIndex == -1)
		cookieEndIndex = document.cookie.length;
    return unescape(document.cookie.substring(cookieStartIndex +
					      prefix.length,
					      cookieEndIndex));
}

// setCookie(name, value, [expires], [path], [domain], [secure])
// sets / overwrites a cookie value.

function setCookie (name, value) {  
   var argv = setCookie.arguments;  
   var argc = setCookie.arguments.length;  
   var expires = (argc > 2) ? argv[2] : null;  
   var path = (argc > 3) ? argv[3] : null;  
   var domain = (argc > 4) ? argv[4] : null;  
   //var secure = (argc > 5) ? argv[5] : true;  
   var secure = (argc > 5) ? argv[5] : false;  

   document.cookie = name + "=" + escape (value) + 
   ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) + 
   ((path == null) ? "" : ("; path=" + path)) +  
   ((domain == null) ? "" : ("; domain=" + domain)) +    
   ((secure == true) ? "; secure" : "");
}

function setPersistentCookie (name, value, path) {
  var today = new Date();
  // Set Cookie expiry date 2 years ahead
  setCookie(name, value, new Date(today.getTime() + 2 * 365 * 24 * 60 * 60 * 1000),path);
}    

function setNonpersistentCookie (name, value, path) {
  var today = new Date();
  // Set Cookie expiry 30 min ahead
  setCookie(name, value, new Date(today.getTime() + 30 * 60 * 1000), path);
}

function deleteCookie(name,path) {
  var today = new Date();
  // Set Cookie expiry 10 mins ago
  setCookie(name, '', new Date(today.getTime() - 10), path);
}