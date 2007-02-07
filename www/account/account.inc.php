<?php
/**
 * Common functions in user administration.
 * 
 * Htpasswd syntax is <code>username:MD5(pwd):Full Name:email@address</code>.
 *
 * @package account
 */
if (!class_exists('Command')) require(dirname(dirname(__FILE__)).'/conf/Command.class.php');
if (!class_exists('Validation')) require(dirname(dirname(__FILE__)).'/plugins/Validation/validation.inc.php');

if (!getConfig('users_file')) {
	trigger_error('Repos user administration is not enabled.', E_USER_ERROR);
}
// Relevant configuration entries
define('USERS_PATH', getConfig('admin_folder').getConfig('users_file'));
define('LOCAL_PATH', getConfig('local_path'));
// Repos convention for the per-user htpasswd file
define('REPOSITORY_USER_FILE_NAME', 'repos-password.htp');

// TODO validation Rules

/**
 * Runs the server password command to create a new auth file line.
 * @return String the BASIC auth line <code>username:MD5(pwd)</code>, with no trailing newline
 */
function accountGetEncryptedPassword($username, $password) {
	$htpasswd = 'htpasswd';
	$c = new Command($htpasswd);
	$c->addArgOption('-nbm');
	$c->addArg($username);
	$c->addArg($password);
	$c->exec();
	$result = $c->getOutput();
	if ($c->getExitcode() != 0) trigger_error('Could not generate password. '.implode("\n", $result), E_USER_ERROR);
	return $result[0];
}
 
/**
 * Updates the apache htpasswd file with a new password,
 * without changing the user profile in the repository.
 *
 * @param String $username existing user
 * @param String $email optional email address that must match the username
 * @return String the new password, false if username or username+password not found
 */
function resetPassword($username, $email='') {
	$password = getRandomPassword($username);
	$pass = accountGetEncryptedPassword($username, $password);
	
	$pattern = preg_quote($username, '/').':[^:]+';
	if ($email) { // require matching email
		$pattern .= ':[^:]*:'.preg_quote($email); // note that this also matches empty email if colons are there
	}
	$pattern = '/^'.$pattern.'/';
	
	$tempfile = System::getTempFile('admin');
	$tmp = fopen($tempfile, 'w');
	$f = fopen(USERS_PATH, 'r');
	$found = false;
	while (!feof($f)) {
        $buffer = fgets($f);
        if (preg_match($pattern, $buffer)) {
        		$found = true;
        		$buffer = "$pass\n"; // TODO email and full name not preserved
        }
        fwrite($tmp, $buffer);
   }
	fclose($f);
	fclose($tmp);
	if ($found) {
		System::deleteFile(USERS_PATH);
		rename($tempfile, USERS_PATH);
		return $password;	
	}
	return false;
}

/**
 * Generate a 8 character temporary password
 *
 * @param String $username
 * @return String password
 */
function getRandomPassword($username) {
	// TODO real randomizer
	return strtolower(substr(base64_encode(microtime()), 2, 8));
}

?>
