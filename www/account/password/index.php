<?php
/**
 * Updates the htpasswd contents.
 * 
 * If the repository contains an invalid password file,
 * first create a temporary password, then go and fill
 * in the password on this page.
 *
 * @package account
 */
require('../../conf/Presentation.class.php');
require('../../open/SvnOpenFile.class.php');
require('../../edit/SvnEdit.class.php');
require('../account.inc.php');

class PasswordRule extends Rule {
	var $repeatfield; 
	function PasswordRule($fieldname='password', $repeatfieldname='passwordrepeat') {
		$this->repeatfield = $repeatfieldname;
		$this->Rule($fieldname, $message);
	}
	function validate($value) {
		if (!$value) return null;
		if (strlen($value) < 6) return 'Must be at least 6 characters';
		if (!isset($_REQUEST[$this->repeatfield])) return 'The "repeat password" field must be filled in to change password';
		if ($value != $_REQUEST[$this->repeatfield]) return 'The password fields do not match';
		$this->_value = $value;
		return null;
	}
}

$username = getReposUser();

$passwordfile = new SvnOpenFile(getTarget());
if ($passwordfile->getStatus() != 200) trigger_error('Could not open password file '.getTarget(), E_USER_ERROR);
$contents = $passwordfile->getContents();
$fullname = accountGetFullName($contents);
$email = accountGetEmail($contents);

// password form should be POSTed
if (isset($_REQUEST[SUBMIT])) {
	Validation::expect('username', 'fullname', 'email', 'password', 'passwordrepeat');
	
	$message = array();
	
	$pass = new PasswordRule();
	if ($pass->getValue()) {
		$newcontents = explode(':',accountGetEncryptedPassword($username, $pass->getValue()).":$fullname:$email");
		$message[] = 'changed password';
	} else {
		$newcontents = explode(':',trim($contents));
	}
	
	$newFullname = $_REQUEST['fullname'];
	if ($newFullname!=$fullname) {
		$newcontents[2] = $newFullname;
		$message[] = 'changed name';
		$fullname = $newFullname;
	}
	
	$newEmail = $_REQUEST['email'];
	if ($newEmail!=$email) {
		if (!isset($newcontents[2])) $newcontents[2] = '';
		$newcontents[3] = $newEmail;
		$message[] = 'changed email';
		$email = $newEmail;
	}
	
	$newPasswordLine = implode(':', $newcontents)."\n";
	if (trim($newPasswordLine) == trim($contents)) {
		trigger_error('The form was submitted but there are no changes.', E_USER_ERROR);
	}
	
	savePassword($newPasswordLine, implode(', ', $message));
		
} else {
	$template = Presentation::getInstance();
	$template->assign('isEdit', !isset($_GET['view']) && $passwordfile->isLatestRevision());
	$template->assign('username', $username);
	$template->assign('fullname', $fullname);
	$template->assign('email', $email);
	$template->assign_by_ref('file', $passwordfile);
	$template->display();
}

function savePassword($filecontents, $message) {
	$wc = System::getTempFolder('acl');
	$checkout = new SvnEdit('checkout');
	$checkout->addArgUrl(getParent(getTargetUrl()));
	$checkout->addArgPath($wc);
	$checkout->exec();
	$f = fopen($wc.basename(getTarget()), w);
	fwrite($f, $filecontents);
	fclose($f);
	$commit = new SvnEdit('commit');
	$commit->addArgPath($wc);
	$commit->setMessage($message);
	$commit->exec('New passwor file committed');
	
	$p = Presentation::getInstance();
	$p->assign('redirect', '/?logout');
	
	displayEdit(Presentation::getInstance());
	// Mau cause error "svn: DELETE of '/data/!svn/act/...': authorization failed"
	// if the new password is enforced by a post-commit hook that the commit waits for
}

// doesn't seem to work that well
function savePasswordUsingService($filecontents, $message) {
	if (!strpos($filecontents, ':')) trigger_error("Invalid password file contents: $filecontents", E_USER_ERROR);
	$url = getWebapp().'edit/upload/';
	$parameters = array(
		'target' => getTarget(),
		'usertext' => $filecontents,
		'message' => $message,
		'fromrev' => 'HEAD',
		'type' => 'txt'
	);
	$edit = new ServiceRequestEdit($url, $parameters);
	$edit->setCustomHttpMethod('POST');
	$edit->exec();
	echo ($edit->getResponse());
	if ($edit->getStatus() != 200) trigger_error("Server error. Could not save new password file, got status ".$edit->getStatus());
}

?>
