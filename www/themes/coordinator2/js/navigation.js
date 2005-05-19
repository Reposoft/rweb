// ** window-agnostic navigation functionality **
// Sets two global variables:
// - isPopup (boolean) true if this window is a popup
// - homeWin (document) refers to the main document or frameset that hosts shared scripts
var isPopup = (window.opener!=null);
var homeWin = this;

// return a reference to the main document
function getHomeWin() {
	var h = this;
	if (isPopup)
		h = window.opener; // returns Window that created popup
	if (h.parent==null)
		return h;
	return h.parent;
}
homeWin = getHomeWin();

// close this view and assume no changes in referrer
function closeView() {
	if (isPopup)
		window.close();
	else
		history.back(1);
}

// close this view and assume changes in referrer
// This is not the same as when done at serverside, where a form submit is assumed
function closeWindowReloadOpener() {
	if (isPopup) {
		window.opener.location.reload(1);
		window.close();
		return;
	} 
	var ref;
	if (homeWin==this)
		ref = window.referrer;
	else
		ref = homeWin.getMainReferrer();
	if (ref==null)
		closeView();
	else
		window.location.href = referrer;
}

// Show popup
// param url: The url in the new window
// param size: For example 'width=400,height=338'
function popup(url,size) {
	url = appendQueryParameter(url,'view','popup');
	if (isPopup)
		location.href = url;
	else
		showPopup(url,size,"");
}

function showPopup(url,size,name) {
	var popupWin = window.open(
		url,
		name,
		'scrollbars=1,toolbars=1,location=1,statusbars=1,menubars=1,resizable=1,top=100,left=100,'+size);
	popupWin.focus();
}

// Loads a new main contents page
function setMain(relativeUrl) {
	if (homeWin==this) {
		location.href = relativeUrl;
		return;
	}
	if (isPopup)
		window.close();
	homeWin.setMain(relativeUrl);
}

// ask the user to confirm before redirect. on cancel do nothing.
function confirmRedirect(localizedQuestion, urlIfOk) {
   if (confirm(localizedQuestion))
   		this.window.location = urlIfOk;
}

// Sets the query string and reloads
// for setting the relative url, use popup or setMain(relativeUrl)
function setSearch(search) {
	this.window.location.search = search;
}

// helper function: append query parameter to url and return it
function appendQueryParameter(url,name,value) {
	if (url.toString().indexOf('?')==-1)
		url += '?';
	else
		url += '&';
	return url + name + '=' + value;
}

// ------ debug info ------------
function test() {
	alert(
		  'Popup: ' + isPopup + ' \n' +
		  'Found main: ' + (homeWin!=null) + ' \n' +
		  'Main\'s href: ' + homeWin.location.href
	);
}
//test();