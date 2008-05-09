<?php
/**
 * Updates the server htpasswd file with a random password for a specific user.
 * 
 * This page is accessible for all users (as a lost-my-password function)
 * but unlike the accountreset/ reset function it does not display the new password
 * (it sends an email to the registered address)
 * and it requires a matching email address.
 *
 * @package
 */
require( dirname(dirname(__FILE__)).'/account.inc.php' );
require( ReposWeb.'/conf/Presentation.class.php' );

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
	$newpass = resetPassword($username, $email, $fullname); // extract fullname in pass-by-reference variable
	if (!$newpass) {
		trigger_error('No user found with username "'.$username.'" and e-mail "'.$email.'".', E_USER_WARNING);
		exit;
	}
	
	$result = accountSendPasswordEmail($username, $newpass, $email, $fullname);
	if ($result===false) showResult("Message to ".($fullname ? $fullname : $username).":\nAdministration E-mail not enabled, new password is $newpass");
	elseif ($result) showResult("Outgoing emails are disabled on this server. Here's what should have been sent: \n\n$result");
	else showResult("A new password has been emailed to address $email.");
}

function showResult($message) {
	$p = new Presentation();
	$p->assign('message', $message);
	$p->display($p->getLocaleFile(dirname(__FILE__).'/done'));
}

?>
