// Integration with trac at /trac on the same host
(function() {

var url = '/trac/';
var project = '';
var trac = url + project;
var icon = '/repos-plugins/trac-integration/trac_logo_32.png';
// unless there's access control we could use the leftmost 30px of this one: /trac/box/chrome/common/trac_logo_mini.png

function cmd() {
	var a = $('<a id="trac"/>').addClass('command').html('Trac').attr('href',trac);
	a.css('background-image', 'url("' + icon + '")');
	return a;
}

Repos.service('home/', function() {
	cmd().appendTo('#commandbar');	
});

})();
