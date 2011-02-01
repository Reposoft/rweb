// Integration with useradmin (webuseradmin.com) at standard repos server URI
(function() {

var url = '/administration/useradmin/';
var icon = url + 'images/user_green.png';

function cmd() {
	var a = $('<a id="useradmin"/>').addClass('command').html('account&nbsp;administration').attr('href',url);
	a.css('background-image', 'url("' + icon + '")');
	return a;
}

Repos.service('home/', function() {
	cmd().appendTo('#commandbar');	
});

})();
