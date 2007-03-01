<?php
/**
 * Common functions in user administration,
 * for Repos installations that use apache password files.
 * 
 * For authentication, regardless of apache backend, use login.inc.php.
 * 
 * Htpasswd syntax is <code>username:MD5(pwd):Full Name:email@address</code>.
 * Location of the file is specified in repos.properties.
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

function accountGetAuthLine($username) {
	$f = fopen(USERS_PATH, 'r');
	$found = false;
	while (!$found && !feof($f)) {
      $buffer = fgets($f);
		if (strBegins($buffer, "$username:")) $found = $buffer;
	}
	fclose($f);
	return $found;
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
function resetPassword($username, $email=null) {
	$password = getRandomPassword($username);
	$pass = accountGetEncryptedPassword($username, $password);
	
	$pattern = preg_quote($username, '/').':[^:]+';
	if ($email!==null) { // require matching email
		if (strlen($email)==0) trigger_error("Parameter 'email' is empty.", E_USER_ERROR);
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
	preg_match('/(\w+:\/\/)([^\/]+)\/.*/', $repository, $matches);
	$hostname = $matches[2];
	$host = $matches[1].$hostname.'/';

	$subject = "Your Repos account $username";
	$body = "$fullname,

A temporary password has been generated at $hostname
for your account $username:
$password

You can log in at $host?login.
After that, please change password from the administration folder.
Or proceed to edit the password file directly at:
{$webapp}edit/?target=/".urlencode($username)."/administration/".REPOSITORY_USER_FILE_NAME."

";
	$body = str_replace("\r\n", "\n", $body);
	
        $headers = 'MIME-Version: 1.0'."\r\n";
        //$headers .= 'Content-Type: text/plain'."\r\n";
	$headers .= 'Content-Type: text/plain; charset=utf-8'."\r\n"; 
        $headers .= 'Content-Transfer-Encoding: 8bit'."\r\n";
        $headers .= "From: Repos Administrator <$from>\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
	
	if (!System::isWindows()) $headers = str_replace("\r\n", "\n", $headers);

   // done
	if (!$emailEnable) return false;
	if (mail($email,$subject,$body,$headers)) {
		return '';
	} else {
		return $body;
	}
}


?>
