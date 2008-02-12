
//load('../index/index.xml');
load('../../../target/test/samples/index.html');
// Load function should define a test document (global variable) T.

// how to wait for async load?
window.setTimeout( function() {

assert(true);
assert($('a',T).size(), 'Should find links in content page');
assert($('h2',T).size(), 1, 'Headline should be level 2 (project is level 1 even if we dont display that)');

// end wait for async load
}, 1000 );
