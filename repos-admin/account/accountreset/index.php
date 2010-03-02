<?php
/**
 * Updates the server htpasswd file with a random password for a specific user.
 *
 * Currently quire same as the users/reset/ but in repos 1.1 this was the admin
 * function and users/reset/ was lostpassword for everyone.
 * 
 * Requires administrator access, @see admin-authorize.inc.php
 * 
 * @package admin
 */

require( '../../account/account.inc.php' );
require('../../admin-authorize.inc.php'); // this script updates local user file

Validation::expect('username');
adminResetPassword($_GET['username']);

function adminResetPassword($username) {
	// Note that this might have been requested without authenticate ("forgot password" functionality)
	// so because we don't have confirmation emails before reses malicious users can reset
	// other user's passwords, and if administration e-mail is not enables noone will know.
	// However the display of passwords below is protected
	// and anyone can always do accountrevert to restore the existing password.
	$newpass = resetPassword($username);
	if (!$newpass) {
		trigger_error('No user found with username "'.$username.'"', E_USER_WARNING);
	}

	$auth = accountGetAuthLine($username);
	$email = accountGetEmail($auth);
	$fullname = accountGetFullName($auth);
	$sent = false;
	if ($email) $sent = accountSendPasswordEmail($username, $newpass, $email, $fullname);
	if ($sent===false) {
		// WARNING: password will be displayed in cleartext
		if (!isLoggedIn()) trigger_error('E-mail not enabled, no admin access', E_USER_ERROR);
		showResult("Administration E-mail not enabled, new password is $newpass");
	} elseif ($sent) {
		// WARNING: password will be displayed in cleartext
		if (!isLoggedIn()) trigger_error('E-mail failure, no admin access', E_USER_ERROR);
		showResult("Could not send e-mail. Please forward this to the user: \n\n$sent");
	}
	else showResult("A new password has been emailed to ".
		(isLoggedIn() ? "address $email." : "the stored address"));
}

function showResult($message) {
	header('Content-Type: text/plain');
	echo $message;
}

?>
