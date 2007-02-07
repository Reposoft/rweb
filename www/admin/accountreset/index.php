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
	$result = resetPassword($username);
	if (!$result) {
		trigger_error('No user found with username "'.$username.'"', E_USER_WARNING);
	}
	// email the new password
	echo ("The temporary password for '$username' is: $result");
}

?>
