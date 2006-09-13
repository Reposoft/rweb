// to be included in the test suite HTML so that all tests can do
// | assertEval | top.setUp() ||

//if (typeof(top.setUp)!='undefined') alert ("Warning: top.setUp() already exists. Overwriting:\n" + top.setUp);

function prepareTests() {
	top.setUp = function(test) {
		//test.browserbot.getCurrentWindow().location.href = 'http://test:test@test.repos.se/testrepo/test/trunk/';
		// now you need to login the browser manually the first time, but with the URL-based login firefox needs a click on OK in a confirm box
		test.browserbot.getCurrentWindow().location.href = '/testrepo/test/trunk/';
		test.doWaitForPageToLoad('5000');
		return '';
		//return 'setUp error. Could not load repository homepage. #contents is not present.';
	};
}

prepareTests();
