// $Id$
// Copyright (c) 2006 Staffan Olsson (http://www.repos.se)
// repos.se client side scripting platform

function ReposScriptSetup() {
	var version = '$Rev$';
	
	// var nocache = false; // for production
	var nocache = new Date().valueOf(); // for development
	
	var parentTag = document.getElementsByTagName("head")[0];
	
	this.defaultNamespace = "http://www.w3.org/1999/xhtml";
	
	// plugins to load from the Repos class (shared/repos.js)
	this.commonPlugins = new Array(
		'tmt-validator/setup.js',
		'geturl/geturl.js');
	
	this.path = "";
	
	this.require = function(scriptUrl) {
		if (nocache) {
			scriptUrl += '?'+nocache;	
		}
		if (scriptUrl.indexOf('/')!=0) {
			scriptUrl = this.path + scriptUrl;	
		}
		var s = this.createElement("script");
		s.type = "text/javascript";
		s.src = scriptUrl;
		parentTag.appendChild(s);
	}
	
	this.createElement = function(tagname) {
		if (document.createElementNS) {
			return document.createElementNS(this.defaultNamespace, tagname);
		} else { // IE does not support createElementNS
			return document.createElement(tagname);	
		}
	}
	
	this._getPath = function() {
		var scripts = document.getElementsByTagName("script");
		for (i=0; i<scripts.length; i++) {
			if (scripts[i].src && scripts[i].src.match(/head\.js(\?.*)?$/)) {
				return scripts[i].src.replace(/head\.js(\?.*)?$/,'');
			}
		}
	}
	
	this.run = function() {
		this.path = this._getPath();
		this.require("shared/prototype/prototype-1.4.0.js");
		//this.require(path+"shared/scriptaculous-1.6.2/scriptaculous.js");
		this.require("shared/repos.js");
	}
}

var reposScriptSetup = new ReposScriptSetup(); // global, no 'var'
reposScriptSetup.run();

// this is needed for the validator load problem
var _head_pageLoaded = false;
function head_setPageLoaded() { _head_pageLoaded = true; }
function head_isPageLoaded() { return _head_pageLoaded; }
window.onload = head_setPageLoaded; // overwriting any existing event handler
