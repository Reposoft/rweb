<?php
/**
 * Common functions in user administration, for Repos installation that use apache password files.
 * 
 * Htpasswd syntax is <code>username:MD5(pwd):Full Name:email@address</code>.
 *
 * @package account
 */
if (!class_exists('Command')) require(dirname(dirname(__FILE__)).'/conf/Command.class.php');
if (!class_exists('Validation')) require(dirname(dirname(__FILE__)).'/plugins/validation/validation.inc.php');

if (!getConfig('users_file')) {
	trigger_error('Repos user administration is not enabled.', E_USER_ERROR);
}
// Relevant configuration entries
define('USERS_PATH', getConfig('admin_folder').getConfig('users_file'));
define('LOCAL_PATH', getConfig('local_path'));
// Repos convention for the per-user htpasswd file
define('REPOSITORY_USER_FILE_NAME', 'repos-password.htp');

// TODO validation Rules

function accountGetUsernameRequiredRule($fieldname='username') {
	return new RuleEreg('username', "Need a valid username.", '.+'); // our current username limitation
}

function accountGetEmailRequiredRule($fieldname='email') {
	return new RuleEreg('email', "Need a valid e-mail address.", '.+@.+\.[a-z]+');
}

/**
 * @return String the Full Name part of the password entry, empty string if not set
 */
function accountGetFullName($authFileLine) {
	list($user, $pass, $full, $email) = explode(":", $authFileLine, 4);
	return $full;
}

/**
 * @return String the email part of the password entry, empty string if not set
 */
function accountGetEmail($authFileLine) {
	list($user, $pass, $full, $email) = explode(":", $authFileLine, 4);
	return $email;
}

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
		$pattern .= ':[^:]*:'.preg_quote($email).''; // note that this also matches empty email if colons are there
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
        		$pass .= ':'.accountGetFullName(trim($buffer)).':'.accountGetEmail(trim($buffer));
        		$buffer = "$pass\n";
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

/**
 * Very basic email functionality for new passwords.
 * @return boolean false if emailing is not enabled in configuration,
 * 	empty String if mail was successfuly sent,
 * 	String with message body if mail sending failed.
 */
function accountSendPasswordEmail($username, $password, $email, $fullname=null) {
	$emailEnable = true;
	$from = getConfig('administrator_email');
	if (!$from) $emailEnable = false; // don't send email
	
	if (!$fullname) $fullname = $username;
	// protect from injection
	$fullname = htmlspecialchars($fullname);
	if (htmlspecialchars($email)!=$email) trigger_error('Invalid e-mail address '.$email, E_USER_ERROR);
	
	$webapp = getWebapp();
	$repository = getRepository();
	preg_match('/(\w+:\/\/[^\/]+\/).*/', $repository, $matches);
	$host = $matches[1];
	$subject = "Your Repos account $username";
	$body = "$fullname,

A temporary password has been generated 
for your account $username:
$password

You can log in at at $host?login.
After that, please change password from the administration folder.
Or access the user password file directly at:
{$webapp}edit/?target=/".urlencode($username)."/administration/".REPOSITORY_USER_FILE_NAME."

";
	$headers = 'From: ' . $host . "\r\n" .
   'Reply-To: ' . $from . "\r\n" .
   'X-Mailer: Repos PHP/' . phpversion();
	
   // done
	if (!$emailEnable) return false;
	if (mail($email,$subject,$body,$headers,null)) {
		return '';
	} else {
		return $body;
	}
}


?>
