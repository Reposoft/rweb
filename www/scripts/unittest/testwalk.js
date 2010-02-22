// simple transition from old testwalk tests to qunit

var webapp = '/repos-web/';
var path = webapp + 'scripts/unittest/';

$(document).ready(function() {
	$('body').append('<h1 id="qunit-header">QUnit example</h1>');
	$('body').append('<h2 id="qunit-banner"></h2>');
	$('body').append('<h2 id="qunit-userAgent"></h2>');
	$('body').append('<ol id="qunit-tests"></ol>');
});

document.write('<script type="text/javascript" src="'+path+'qunit.js"></script>');

document.write('<link type="text/css" rel="stylesheet" href="'+webapp+'style/global.css"></link>');
document.write('<link type="text/css" rel="stylesheet" href="'+path+'qunit.css"></link>');

// old assert function, not detecting assert-true with message
function assert() {
	if (arguments.length == 1) {
		return ok.apply(this, arguments);
	}
	var a = arguments[0];
	arguments[0] = arguments[1];
	arguments[1] = a;
	equals.apply(this, arguments);
}
