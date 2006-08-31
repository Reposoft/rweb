// repos.se script setup, targeted for all <head>s

var _headTag = document.getElementsByTagName("head")[0];
var _namespace = "http://www.w3.org/1999/xhtml";
var _reposWeb = "/repos";

// var _nocache = false;
var _nocache = Math.random();

// public flag to check if the page has loaded (is there no built in property for this?)
var repos_page_loaded = false;
function repos_setPageLoaded() { repos_page_loaded = true; }
// assuming that nothing is in the onload before this
window.onload = repos_setPageLoaded;

function head_importStyles() {
	
}

function head_importScripts() {
	head_addReposScript('/scripts/tmt-validator/setup.js');
	head_addReposScript('/scripts/geturl/geturl.js');
}

/**
 * @param url URL (starting with slash) from repos www root folder
 */
function head_addReposScript(url) {
	head_addServerScript(_reposWeb + url);
}

/**
 * @param url URL (starting with slash) from server root
 */
function head_addServerScript(url) {
	head_addScript(_headTag, url);
}

function head_addScript(parentTag, absoluteUrl) {
	if (_nocache) {
		absoluteUrl += '?'+_nocache;	
	}
	var s = head_createElement("script");
	s.type = "text/javascript";
	s.src = absoluteUrl;
	parentTag.appendChild(s);
}

function head_createElement(tagname) {
	var e;
	if (document.createElementNS) {
		e = document.createElementNS(_namespace, tagname);
	} else { // IE does not support createElementNS
		e = document.createElement(tagname);	
	}
	return e;
}

head_importStyles();
head_importScripts();