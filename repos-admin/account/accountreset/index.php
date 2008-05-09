<?php
/**
 * Updates the server htpasswd file with a random password for a specific user.
 *
 * Currently quire same as the users/reset/ but in repos 1.1 this was the admin
 * function and users/reset/ was lostpassword for everyone.
 * 
 * @package admin
 */

require( '../../account/account.inc.php' );

Validation::expect('username');
adminResetPassword($_GET['username']);

function adminResetPassword($username) {
	$newpass = resetPassword($username);
	if (!$newpass) {
		trigger_error('No user found with username "'.$username.'"', E_USER_WARNING);
	}

	$auth = accountGetAuthLine($username);
	$email = accountGetEmail($auth);
	$fullname = accountGetFullName($auth);
	$sent = false;
	if ($email) $sent = accountSendPasswordEmail($username, $newpass, $email, $fullname);
	if ($sent===false) showResult("Administration E-mail not enabled, new password is $newpass");
	elseif ($sent) showResult("Could not send e-mail. Please forward this to the user: \n\n$sent");
	else showResult("A new password has been emailed to address $email.");
}

function showResult($message) {
	header('Content-Type: text/plain');
	echo $message;
}

?>
