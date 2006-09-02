// repos getUrl (c) Staffan Olsson
//Repos.require('shared/repos-gui.js');

// the reference to the displayed div, when displayed
var objGetUrl;
// popup titlebar
var _geturl_title_prefix = 'URL: ';

function getUrlClose() {
	objGetUrl.style.display = 'none';
	// remove from the dom
	objGetUrlParent.removeChild(objGetUrl);
	objGetUrl = undefined;
}

function getUrl(objLink) {
	showUrl(objLink.href, getUrlText(objLink));
}

function getUrlText(objLink) {
	var text = '';
	var c = objLink.childNodes;
	for (i=0; i<c.length; i++) {
		if (c[i].data) {
			text = text + c[i].data;	
		} else {
			for (j=0; j<c[i].childNodes.length; j++) {
				text = text + c[i].childNodes[j].data;
			}
		}
	}
	return text;
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
	var popupHeight = 50;
	var popupLeft = geturl_calculateLeft(popupWidth);
	// create the popup
	win = new Window('window_id', {className: "alphacube", title: titleText, width:popupWidth, height: popupHeight});
	objGetUrl = win.getContent();
	// create the input box that will contain the link
	objText = geturl_makeText(Math.floor(absoluteUrl.length * 1.1), absoluteUrl);
	objGetUrl.appendChild(objText);
	// show
	win.setDestroyOnClose();
	win.showCenter();
	// select the url so the user can do Ctrl+C
	objText.select();
}

function geturl_makeText(size, value) {
	var text = geturl_createElement('input');
	text.type = 'text';
	text.value = value;
	text.size = size;
	text.style.border = 'none';
	text.style.background = 'none';
	text.style.fontFamily = 'Arial, Helvetica, sans-serif';
	text.style.fontSize = '11px';
	text.style.color = '#333333';
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

getKeyCode = function(ev)
	{
		if(ev)			//Moz
		{
			return ev.keyCode;
		}
		if(window.event)	//IE
		{
			return window.event.keyCode;
		}
	};