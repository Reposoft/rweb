<?php
/**
 * Allow the administrator to test the different application emails.
 *
 * @package admin
 */
require('../../conf/Report.class.php');
require('../../account/account.inc.php');

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
	'Test password email to <input name="email" type="text" size="40" value="'.$adminemail.'"/>'.
	'<input type="submit" name="submit" value="password"/></form>');
	$r->display();
}

function emailTestPassword($toEmail) {
	$result = accountSendPasswordEmail('[test admin email]', 'Abc123@#%&/()=?', $toEmail, 'Administrator');
	echo ("Sample password email was sent to $toEmail with password 'Abc123@#%&/()=?'");
}

?>
