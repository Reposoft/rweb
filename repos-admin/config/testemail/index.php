<?php
/**
 * Allow the administrator to test the different application emails.
 *
 * @package admin
 */

//not needed when we include account.inc//require( '../../reposweb.inc.php' );
require( '../../account/account.inc.php' );
require( ReposWeb.'conf/Report.class.php' );

if (isset($_GET[SUBMIT])) {
	if (!isset($_GET['email'])) trigger_error('"email" not set');
	$email = $_GET['email'];
	if ($_GET[SUBMIT]=='password') emailTestPassword($email);
	else echo('unknown test: '.$_GET[SUBMIT]);
} else {
	emailShowInfo();
}

function emailShowInfo() {
	$adminemail = getConfig('administrator_email');
	$r = new Report('Test Repos application e-mails');
	// reset password
	if (!$adminemail) $r->warn('administrator_email not set');
	$r->info('<form action="./" method="get">'.
	'Test password email to address <input name="email" type="text" size="40" value="'.$adminemail.'"/>'.
	'<input type="submit" name="submit" value="password"/> (only a test, does not affect accounts)</form>');
	$r->display();
}

function emailTestPassword($toEmail) {
	$r = new Report('Test Repos application e-mails');
	$result = accountSendPasswordEmail('[test admin email]', 'Abc123@#%&/()=?', $toEmail, 'Administrator');
	if ($result===false) $r->info('There is no administration_email set in application properties.');
	if ($result) {
		$r->debug($result);
		$r->fail('Server says mail could not be sent');
	} else {
		$r->ok("Sample password email was sent to $toEmail with password 'Abc123@#%&/()=?'");
	}
}

?>
