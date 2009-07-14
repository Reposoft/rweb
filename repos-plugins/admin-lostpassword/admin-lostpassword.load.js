
(function() {

var a = '/repos-admin/';
var url = a+'account/reset/';

function cmd() {
	return $('<a id="lostpassword"/>').addClass('command').html('lost&nbsp;password').attr('href',url);
}

Repos.service('account/login/', function() {

	if ($('h2').text() != 'Login failed') return;
	
	var msg = $('<p>Forgot your password? If you have an e-mail address stored in your Repos preferences, '+
	'you can use the <a href="'+url+'">lost password</a> functionality. '+
	'Otherwise ask an administrator to reset your password.</p>');
	msg.insertBefore('#footer');

	cmd().appendTo('#commandbar');
	
});

Repos.service('home/', function() {
	if (!Repos.getUser()) {
		cmd().appendTo('#commandbar');	
	}
});

})();
