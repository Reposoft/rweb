<?php
/**
 * Updates the server htpasswd file with a random password for a specific user.
 *
 * @package admin
 */
require('../../account/account.inc.php');

Validation::expect('username');
adminResetPassword($_GET['username']);

function adminResetPassword($username) {
	$newpass = resetPassword($username);
	if (!$newpass) {
		trigger_error('No user found with username "'.$username.'"', E_USER_WARNING);
	}

	$result = accountSendPasswordEmail($username, $newpass, $email, $fullname);
	if ($result===false) showResult("Administration E-mail not enabled, new password is $password");
	elseif ($result) showResult("Could not send the email with the following contents: \n\n$result");
	else showResult("A new password has been emailed to address $email.");
}

function showResult($message) {
	header('Content-Type: text/plain');
	echo $message;
}

?>
