
var webapp = '/repos-web/';
var path = webapp + 'scripts/unittest/';

document.write('<script type="text/javascript" src="'+path+'testwalk/assert.js"></script>');
document.write('<script type="text/javascript" src="'+path+'testwalk/testwalk.js"></script>');

document.write('<link type="text/css" rel="stylesheet" href="'+webapp+'style/global.css"></link>');
document.write('<link type="text/css" rel="stylesheet" href="'+path+'testwalk/assert.css"></link>');

function load() {
	e = document.createElement('div');
	e.id = 'assertlog';
	document.getElementsByTagName('body')[0].appendChild(e);
}

$ && $(document).ready(load) || window.onload(load);
