<?php
/**
 * Updates the server htpasswd file with a random password for a specific user.
 * 
 * This page is accessible for all users (as a lost-my-password function)
 * but unlike the repos/admin/ reset function it does not display the new password
 * (it sends an email to the registered address)
 * and it requires a matching email address.
 *
 * @package
 */
require('../../conf/Presentation.class.php');
require('../../account/account.inc.php');

$username = accountGetUsernameRequiredRule();
$email = accountGetEmailRequiredRule();

if (isset($_GET[SUBMIT])) {
	Validation::expect('username', 'email');
	userResetPassword($username->getValue(), $email->getValue());
} else {
	$template = Presentation::getInstance();
	$template->display();
}

function userResetPassword($username, $email) {
	$newpass = resetPassword($username, $email);
	if (!$newpass) {
		trigger_error('No user found with username "'.$username.'" and e-mail "'.$email.'".', E_USER_WARNING);
		exit;
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
