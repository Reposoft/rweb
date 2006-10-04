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

// import the unit testing library, this makes sure that there is a TestCase class
var s = document.createElement("script");
s.type = "text/javascript";
s.src = path + "lib/ecmaunit/ecmaunit.js";
document.getElementsByTagName('head')[0].appendChild(s);	

/**
 * Runs a test case and writes the result to a new div named 'testlog'
 */
function testrun(testCaseInstance) {
	var e = document.createElement('div');
	e.id = 'testlog';
	document.getElementsByTagName('body')[0].appendChild(e);

	testCaseInstance.initialize(new HTMLReporter(e));
	testCaseInstance.runTests();
}
