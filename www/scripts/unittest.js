/**
 * Repos script unit testing (c) Staffan Olsson http://www.repos.se
 * @version $Id$
 *
 * Javascript test cases are HTML files that include this in head:
 * <script type="text/javascript" src="[this-file's-path]/unittest.js"></script>
 *
 * and this in body:

<script type="text/javascript">

function TestX() {
    
    this.setUp = function() {
    };
    
	this.testFunctionA = function() {
		this.assertTrue(...);
		this.assertEquals(...);
	};
	
	this.testFunctionB = function() {
		...
	};
	
}
TestX.prototype = new TestCase;
testrun(new TestX());
</script>

 */

// same as ReposPrepare
if (document.documentElement && document.documentElement.namespaceURI && document.createElementNS) {
	document.createElement = function(t) {
		return document.createElementNS(document.documentElement.namespaceURI, t);
	};
}

// replace public functions in Repos shared
if (typeof(Repos)=='undefined') {
	var Repos = {};
}
Repos.reportError = function(error) {
	alert(error);
}

// get the path that this file was included with
var me = /unittest\.js(\?.*)?$/;
var path = '';
var tags = this.document.getElementsByTagName('head')[0].childNodes;
for (i = 0; i < tags.length; i++) {
	var t = tags[i];
	if (!t.tagName) continue;
	var n = t.tagName.toLowerCase();
	if (n == 'script' && t.src && t.src.match(me)) // located head.js, save path for future use
		path = t.src.replace(me, '');
}
if (path.length < 1) alert("Error: Can not find unittest.js in head. Impossible to derive the include path for libs.");

var _head = document.getElementsByTagName('head')[0];
// import the unit testing library, this makes sure that there is a TestCase class
var s = document.createElement("script");
s.type = "text/javascript";
s.src = path + "lib/ecmaunit/ecmaunit.js";
_head.appendChild(s);	

// import stylesheets
reposPath = path.substr(0, path.lastIndexOf('/', path.length-2)+1);
var c = document.createElement("link");
c.type = "text/css";
c.rel = "stylesheet";
c.href = reposPath + "style/global.css";
_head.appendChild(c);
var d = document.createElement("link");
d.type = "text/css";
d.rel = "stylesheet";
d.href = reposPath + "style/docs.css";
_head.appendChild(d);


var testCase = null;
var loaded = false;

/**
 * Makes sure the test case is executed after all other onload activities.
 */
function testrun(testCaseInstance) {
	testCase = testCaseInstance;
	if (loaded) testexec();
}

/**
 * Runs a test case and writes the result to a new div named 'testlog'
 */
function testexec() {
	if (testCase == null) console.log('No test case specified. Call testrun(my testcase).');
	if (window.console) console.log('running testcase');
	var e = document.createElement('div');
	e.id = 'testlog';
	document.getElementsByTagName('body')[0].appendChild(e);
	testCase.initialize(new HTMLReporter(e));
	testCase.runTests();	
}

function testonload() {
	if (window.console) console.log('page loaded');
	if (testCase==null) {
		loaded = true;
	} else {
		testexec();
	}
}

// use jquery if available
if (typeof($)=='undefined') {
	//var _onload = window.onload;
 	//window.onload = function() { _onload(); testexec(); };
 	// temporary solution 
 	window.onload = function() { testonload(); };
} else {
	$(document).ready( function() { testonload(); } );
}

