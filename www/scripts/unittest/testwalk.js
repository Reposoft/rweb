
var path = '/repos/scripts/unittest/';

document.write('<script type="text/javascript" src="'+path+'testwalk/assert.js"></script>');
document.write('<script type="text/javascript" src="'+path+'testwalk/testwalk.js"></script>');

document.write('<link type="text/css" rel="stylesheet" href="'+path+'testwalk/assert.css"></link>');

function load() {
	e = document.createElement('div');
	e.id = 'assert';
	document.getElementsByTagName('body')[0].appendChild(e);
	assert.setLog('#assert');
}

$ && $().ready(load) || window.onload(load);
