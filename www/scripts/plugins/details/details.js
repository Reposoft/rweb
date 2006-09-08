// repos getUrl (c) Staffan Olsson
//Repos.require('shared/repos-gui.js');

Repos.require('lib/dialog/dialog.js');

Details = Class.create();
Details.prototype = {
	
	initialize: function() {
		
	}
	

}

function _details_decorate() {
		
}

//Event.observe(window, 'load', _details_decorate, false);


// popup titlebar
var _geturl_title_prefix = 'URL: ';
var win = null;

function getUrlClose() {
	win.close();
}

function getUrl(objLink) {
	showUrl(objLink.href, getUrlText(objLink));
}

function getUrlText(objLink) {
	return objLink.innerHTML; // using Prototype extension to String
}

function showUrl(relativeOrAbsoluteUrl, titleText) {
	if (relativeOrAbsoluteUrl.indexOf('..')==0) {
		alert('getUrl can not handle ../ links yet');
		return;						 
	}
	if (titleText == undefined) {
		titleText = 'Uniform Resource Locator';
	}
	if (relativeOrAbsoluteUrl.indexOf('://')>2) {
		showUrlPopup(relativeOrAbsoluteUrl, _geturl_title_prefix + titleText);
	} else {
		showUrlPopup(location.href + relativeOrAbsoluteUrl, _geturl_title_prefix + titleText);
	}
}

function showUrlPopup(absoluteUrl, titleText) {
	// figure out the size and position of the main div
	var popupWidth = geturl_calculateWidth(absoluteUrl);
	var popupHeight = 100;
	var popupLeft = geturl_calculateLeft(popupWidth);
	// create the popup
	win = Repos.createWindow({title: titleText, width: popupWidth, height: popupHeight});
	// maybe Repos.createWindow should automatically set close key
	/* Can't get the keypress observer to work
	win.getContent().onkeypress = function(ev) { // hide on ESC
		if(ev && ev.keyCode == 27) getUrlClose();
		if(window.event && window.event.keyCode == 27) getUrlClose();
	}*/
	// create the input box that will contain the link
	objText = geturl_makeText(Math.floor(absoluteUrl.length * 1.1), absoluteUrl);
	win.setContent(absoluteUrl);//objText);
	//Element.setStyle(win.getContent(), {overflowX: 'hidden', overflowY: 'hidden'});
	// show
	win.show();
	// select the url so the user can do Ctrl+C
	objText.select();
}

function geturl_makeText(size, value) {
	var text = Repos.create('input');
	text.type = 'text';
	text.value = value;
	text.size = size;
	Element.setStyle(text, { border: 'none', fontFamily: 'Arial, Helvetica, sans-serif', fontSize: '11px', color: '#333333'});
	return text;
}

// estimate the width in pixels for the popup to fit the input box
function geturl_calculateWidth(absoluteUrl) {
	var urlLength = absoluteUrl.length;
	// TODO take characters that will be urlescaped into account
	return urlLength * 5 + 30;
}

function geturl_calculateLeft(width) {
	return Math.floor((geturl_getClientWidth() - width) / 2);
}

function geturl_createElement(tagname) {
	if (document.createElementNS) {
		return document.createElementNS("http://www.w3.org/1999/xhtml", tagname);
	} else { // IE does not support createElementNS
		return document.createElement(tagname);	
	}
}

function geturl_getClientWidth() {
	// document.documentElement.clientWidth does not work in IE xml+xslt
	return Math.max(document.documentElement.clientWidth, document.body ? document.body.clientWidth : 0);
}

function showDetails() {
	// get details using AJAX
}
