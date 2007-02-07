<?php
/**
 *
 *
 * @package
 */
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php'); 
require(dirname(dirname(dirname(__FILE__))).'/edit/SvnEdit.class.php');
require(dirname(dirname(__FILE__)).'/account.inc.php');

if (isset($_GET[SUBMIT])) {
	$username = $_GET['username'];
	$email = $_GET['email'];
	$fullname = $_GET['fullname'];
	$password = ''.$username; // TODO generate password
	accountCreateUserFolder(getTargetUrl(), $username, $password, $email, $fullname);
	// TODO redirect to account/acl/?create=username
	displayEdit(Presentation::getInstance());
} else {
	$p = Presentation::getInstance();
	$p->assign('target', '/'); // users are created in repository root
	$p->display();
}

/**
 * Creates initial user home folder according to repos conventions
 *
 * @param String $rootUrl the parent folder in the repository, normally repository root with trailing slash
 * @param String $username name of the folder
 * @param String $password initial password for the htpasswd file
 * @param String $email user's email address for the htpasswd file
 * @param String $fullname user's real name for the htpasswd file
 */
function accountCreateUserFolder($rootUrl, $username, $password, $email='', $fullname='') {
	
	// create local user setup
	$folder = System::getTempFolder('account');
	$trunk = mkdir($folder.'trunk/');
	
	// create user file contents
	$pass = accountGetEncryptedPassword($username, $password);
	// append email and full name like htadmin 1.2.4 does
	$pass .= ":$fullname";
	$pass .= ":$email";
	$pass .= "\n";
	System::createFileWithContents($folder.REPOSITORY_USER_FILE_NAME, $pass);
	
	// structure created, do import
	$url = "$rootUrl$username/";
	$import = new SvnEdit('import');
	$import->addArgPath($folder);
	$import->addArgUrl($url);
	$import->setMessage('Created user account '.$username);
	$import->exec('Created user home folder '.$url.'. The temporary password is "'.$password.'".');
}

?>
