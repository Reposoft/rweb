
Repos.service('account/login/', function() {
	// same parameters as admin plugin
	var a = '/repos-admin/';
	var u = a+'account/reset/';

	var msg = $('<p>Forgot your password? If you have an e-mail address stored in your Repos preferences, '+
	'you can use the <a href="'+u+'">lost password</a> functionality. '+
	'Otherwise ask an administrator to reset your password.</p>');
	msg.insertBefore('#footer');

	var cmd = $('<a id="lostpassword"/>').addClass('command').text('lost password').attr('href',u);
	cmd.appendTo('#commandbar');
});
