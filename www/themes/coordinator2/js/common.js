// load new contents in main frame
function setMain(url) {
	window.frames['main'].location.href = url;
}

// @return the absolute url of the contents page
function getMainUrl() {
	try {
		return window.frames['main'].location.href;
	} catch (notAllowedToSeeUrl) {
		return 'unit1/bb/.jwa';
	}
}

// @return entire url of main's referrer, including query strings
function getMainReferrer() {
	win = window.frames['main'];
	if (win==null)
		win = top;
	return win.referrer;
}

// Go back a specified number of pages in history in main frame
// and reload that page
function reloadMain(stepsBack) {
	if (stepsBack>0) {
		currentHref = getMainUrl();
		window.frames['main'].history.go(-stepsBack);
		reloadMainIfChanged(currentHref);
	} else {
		window.frames['main'].location.reload(1);
	}
}

// Reload main page from server when it is no linger the specified URL
// Used for example in closeWindowReloadOpener to reload after history.back();
function reloadMainIfChanged(fromHref) {;
	if (fromHref!=getMainUrl()) {
		window.frames['main'].location.reload(1);
	} else {
		setTimeout("reloadMainIfChanged('" + fromHref + "')",200);
	}
}

// load updateToolbar
function updateNavigation() {
	var mainUrl = getMainUrl();
	var currentUnit = getActiveUnit();
	var toolName = getActiveTool(mainUrl);
	// tree
	window.frames['tree'].setActive( currentUnit );
	// toolbar
	try {
		frames['toolbar'].setActive( currentUnit, toolName);
	} catch(er) {
		reportScriptError('' + er + ' setting tool for url ' + mainUrl);
	}
}

// @return the name of the active tool given an url
function getActiveTool(url) {
	var eix = url.lastIndexOf('/');
	if (eix < 0) return null;
	var ix = url.lastIndexOf('/',eix-1);
	if (ix < 0) return null;
	return url.substring(ix+1,eix);
}

/**
 * @return String identifier of the current unit, for example 'unit1028'
 */
function getActiveUnit() {
	var url = getMainUrl();
	var ix = url.indexOf('/unit');
	var eix = url.indexOf('/',ix+5);
	if (ix < 0 || eix < 0) return 0;
	return url.substring(ix+1,eix);
}

// old cookies.js
// --------------

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

// old noborder.js
// ---------------

function noborder() {
	//var Alist = document.getElementsByTagName('a');
	//for (var i = 0; i < Alist.length; i++) {
	//	Alist.item(i).onfocus = Alist.blur;
	//}
    for (a in document.links) document.links[a].onfocus = document.links[a].blur;
    for (a in document.images) document.images[a].onfocus = document.images[a].blur;
}

if (document.getElementById) {
    document.onmousedown = noborder;
}

// don't know the point of this function
function alt(e) {
	window.status = "";
	return true;
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
var hasReported = false;
function reportBack(queryString) {
	try {
		pic1 = new Image(1,1); 
		pic1.src = getActiveUnit() + '/' + getActiveTool() + '/showReport.jwa?report-method=image&'+queryString;
	} catch (e) {}
}
