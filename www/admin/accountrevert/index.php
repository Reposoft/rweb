<?php
/**
 * Reads the user password from repository and writes to password file.
 *
 * This operation is done by post-commit hooks,
 * and users that have got a new password by email and want to revert the reset.
 * 
 * @package account
 */
require('../../account/account.inc.php');
require('../../conf/Report.class.php');

$r = new Report('Update user login');
 
if (!isset($_GET['username'])) trigger_error('username required', E_USER_ERROR);
$username = $_GET['username'];

$look = new Command('svnlook');
$look->addArgOption('cat');
$look->addArg(LOCAL_PATH);
$look->addArg('/'.$username.'/administration/'.REPOSITORY_USER_FILE_NAME);
if ($look->exec()) trigger_error('Incorrect username or e-mail address', E_USER_ERROR);
$result = $look->getOutput();

$userpasswd = $result[0];
$r->debug($userpasswd);

// now read the apache file and replace the user's line with the new one
$pattern = '/^'.preg_quote($username).':.+/';

if (!file_exists(USERS_PATH)) trigger_error("Could not access authentication file");
$tmpfile = System::getTempFile('admin');
$tmp = fopen($tmpfile, 'w');
$f = fopen(USERS_PATH, 'r');
$found = false;
while (!feof($f)) {
	$buffer = fgets($f);
	if (preg_match($pattern, $buffer)) {
		if ($found) trigger_error("Found more than one match to user pattern $pattern", E_USER_ERROR);
		fwrite($tmp, $userpasswd."\n");
		$found = true;
	} else {
		fwrite($tmp, $buffer);
	}
}
if (!$found) {
	fwrite($tmp, $userpasswd."\n");
	$r->ok("Wrote new user to password file.");
} else {
	$r->ok("Replaced the password line that matched $pattern.");
}
fclose($f);
fclose($tmp);
System::deleteFile(USERS_PATH);
rename($tmpfile, USERS_PATH);

$r->display();

?>
