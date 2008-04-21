
Repos.service('account/login/', function() {
	// same parameters as admin plugin
	var a = '/repos-admin/';
	var w = Repos.getWebapp();
	var u = a+'users/reset/';

	var msg = $('<p>If you have an e-mail address stored in your Repos preferences, '+
	'you can use the <a href="'+u+'">lost password</a> functionality. '+
	'Otherwise ask an administrator to reset your password.</p>');
	msg.insertBefore('#footer');

	var cmd = $('<a/>').addClass('command').text('lost password').attr('href',u);
	cmd.appendTo('#commandbar');
});
