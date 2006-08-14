/**
	repos Quay
	----------
	(c) Staffan och Kalle at repos.se 2006
	
	Simply include this javascript file in the head of any HTML file.
	Optionally add a Quay button using <a id="quayButton"></a> somewhere in the page.
*/
	document.write('<script type="text/javascript" src="scripts/autosuggest.js"></script>');
	document.write('<link href="css/repos-quay.css" rel="stylesheet" type="text/css" />');

	// the indexed links
	var linkCache = new Array();
	// keys used in event handling
	var Q_KEY = 81;
	var ALT_KEY = 18;
	var ESC_KEY = 27;
	// the autosuggest object instance for the Quay
	var quaySuggest;

	function quay_onKeyDown(e){
		var keyId = (window.event) ? event.keyCode : e.keyCode;
		var altDown = (ALT_KEY) ? true : false;
		
		if(keyId == Q_KEY && altDown){
			showQuay();
		}
		
		if(keyId == ESC_KEY) {
			hideQuay();
		}
	}
	
	// class representing an indexed page link
	function linkObject(anchorNode){
		// text will be an empty string if there is no matches, which means that the link can not be suggested
		var t = "";
		
		// index only link tags that have contents. we need a way for this function to say that an element should not be indexed.
		if (anchorNode.childNodes[0]) {
			// if the link has textual contents, use it as suggestion text
			if (anchorNode.childNodes[0].data) {
				t = anchorNode.childNodes[0].data;
			}
			// also allow images as links
			else if (anchorNode.childNodes[0].tagName && anchorNode.childNodes[0].tagName == "IMG") {
				if(anchorNode.childNodes[0].title){
					t = anchorNode.childNodes[0].title;
				}
				else if(anchorNode.childNodes[0].alt){
					t = anchorNode.childNodes[0].alt;
				}
			}
		}
				
		this.text = t.toString();
		this.url = anchorNode.href.toString();
	}
	
	function getQuayWindow(){
		return document.getElementById("quay");
	}
	
	function getQuayInputBox() {
		return document.getElementById("txtQuaySearch");
	}

	/* if a quayButton exists, do the layout
	<a id="quayButton" title="Go Alt+Q for Quay" accesskey="Q" href="#" onclick="javascript:showQuay()"><img src="images/q1.png"/></a>
	*/
	function printQuayButton() {
		var a = document.getElementById("quayButton");
		if (a) {
			a.title = "Go Alt+Q for Quay";
			// a.accesskey = "Q"; // not needed if using keyboard events
			a.href = "javascript:showQuay()";
			var img = document.createElement("img");
			img.src = "images/q1.png";
			a.appendChild(img);
		}
	}

	/* print the Quay popup, formatted using CSS styles for the tags' IDs
<div id="quay">
	<!-- using onkeypress="return event.keyCode!=13" to disable browser's form handling on enter key -->
	<div id="quay_header">
		<a id="quayHeaderLogo" href="about.html"></a>
		<a id="quayCloseIcon" href="javascript:hideQuay();"></a>
	</div>
	<div id="quay_mainarea">
		<input id="txtQuaySearch" name="textfield" type="text" />
	</div>
</div>
	*/	
	function printQuayDiv() {
		var d = document.createElement("div");
		d.id = "quay";
		var header = document.createElement("div");
		header.id = "quay_header";
		var mainarea = document.createElement("div");
		mainarea.id = "quay_mainarea";
		// create the header elements
		var logo = document.createElement("a");
		logo.id = "quayHeaderLogo";
		logo.href = "about.html";
		header.appendChild(logo);
		var closeicon = document.createElement("a");
		closeicon.id = "quayCloseIcon";
		closeicon.href = "javascript:hideQuay()";
		header.appendChild(closeicon);
		// create the input box
		var txt = document.createElement("input");
		txt.id = "txtQuaySearch";
		txt.name = "textfield";
		txt.type = "text";
		mainarea.appendChild(txt);
		d.appendChild(header);
		d.appendChild(mainarea);
		document.body.appendChild(d);
	}
	
	// <!-- the box that will show matching links -->
	// <div id="autosuggest" style="z-index:65001;"><ul></ul></div>
	function printAutosuggestDiv() {
		var d = document.createElement("div");
		var u = document.createElement("ul");
		d.id = 'autosuggest';
		d.appendChild(u);
		document.body.appendChild(d);
	}
		
	function quay_indexLinks(s){
		var as = document.body.getElementsByTagName("a");
		for(ix in as){
			if(as[ix].childNodes){
				linkCache.push(new linkObject(as[ix]));
			}
		}
	}
	
	// document_onLoad()
	function quay_load() {
		// run the indexing for autosuggest		  
		quay_indexLinks();
		// print the quay HTML
		printQuayButton();
		printQuayDiv();
		printAutosuggestDiv();
		// initiate autosuggest for the Quay inputbox
		quaySuggest = new AutoSuggest(getQuayInputBox(), linkCache);
		// make sure the Quay window is closed
		hideQuay();
	}
	
	// document_onUnload()
	function quay_unload() {
		hideQuay();
	}
	
	function showQuay() {
		var quayWin = getQuayWindow();
		quayWin.style.display = 'block';
		var inputBox = getQuayInputBox();		
		inputBox.focus();
	}
	
	function hideQuay(){
		var qsWin = getQuayWindow();
		qsWin.style.display = 'none';
		quaySuggest.clearInput();
		quaySuggest.hideDiv();
	}
	
	// allow keyboard events to show and hide the quay. Clicking the quay icons works without this.
	document.onkeydown = quay_onKeyDown;
	