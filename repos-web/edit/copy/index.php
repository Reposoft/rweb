<?php
require( dirname(dirname(dirname(__FILE__)))."/conf/Presentation.class.php" );
require( dirname(dirname(__FILE__))."/SvnEdit.class.php" );

// prefix for query params and form fields to be treated as svn properties
define('UPLOAD_PROP_PREFIX', 'prop_');

targetLogin(); // edit operation can not be public

// automatic validation
new FilenameRule('newname');
// svn import: parent folder must exists, to avoid implicit create
$parent = new ResourceExistsRule('tofolder');
// explicit validation of the destination
$tofolder = rtrim($parent->getValue(), '/').'/';// don't require tailing slash from user;
new NewFilenameRule('newname', $tofolder);
$revisionRule = new RevisionRule();

// dispatch
if ($_SERVER['REQUEST_METHOD']=='POST') {
	svnCopy($tofolder); 
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$file = new SvnOpenFile($target, $revisionRule->getValue());
	$file->isWritable(); // check before page is displayed because it might require authentication
	$template->assign_by_ref('file', $file);
	$template->assign('target', $target);
	$template->assign('oldname', getPathName($target));
	$folder = getParent($target);
	if (!$folder) $folder = '/'; // getParent resturns empty string for file in root	
	$template->assign('folder', $folder);
	if (isset($_REQUEST['tofolder'])) {
		$template->assign('tofolder', $_REQUEST['tofolder']);
	} else {
		$template->assign('tofolder', $folder);
	}
	$template->display();
}

function svnCopy($tofolder) {
	Validation::expect('target', 'tofolder', 'newname', 'move', 'message');
	$props = getCustomProperties();
	if (count($props) > 0) {
		if ($_POST['move']==1) {
			trigger_error('Move with propset is not supported', E_USER_ERROR); // no use case yet and we'd have to check out both origin and destination
			exit;
		}
		return svnCopyInWc($tofolder, $props);
	}
	$template = Presentation::background();
	if ($_POST['move']==1) {
		$edit = new SvnEdit('move');
	} else {
		$edit = new SvnEdit('copy');
	}
	$oldUrl = getTargetUrl();
	$newTarget = $tofolder.$_POST['newname'];
	$newUrl = getRepository().$newTarget;
	if (isset($_POST['rev'])) {
		$edit->addArgUrlPeg($oldUrl, $_POST['rev']);	
	} else {
		$edit->addArgUrl($oldUrl);
	}
	$edit->addArgUrl($newUrl);
	$edit->addArgRevpropsFromPost();
	if (isset($_POST['message'])) {
		$edit->setMessage($_POST['message']);
	}
	$edit->exec();
	displayEdit($template, getParent($oldUrl), $newTarget); // old target would sometimes be interesting too, needs additional edit result page logic 
}

/**
 * 
 * @param String $tofolder already validated to not exist
 * @param array(String) $props
 */
function svnCopyInWc($tofolder, $props) {
	$oldUrl = getTargetUrl();
	$newTarget = $tofolder.$_POST['newname'];
	$newTargetParent = getRepository().$tofolder;
	$workingCopy = System::getTempFolder('copy_propset');
	$workingCopyFilePath = $workingCopy.$_POST['newname'];
	// start
	$template = Presentation::background();
	// propset can only be done in wc
	$checkout = new SvnEdit('checkout');
	$checkout->addArgOption('--depth=empty');
	$checkout->addArgUrl($newTargetParent);
	$checkout->addArgPath($workingCopy);
	$checkout->exec('Check out parent folder of destination');
	// create the local copy
	$edit = new SvnEdit('copy');
	if (isset($_POST['rev'])) {
		$edit->addArgUrlPeg($oldUrl, $_POST['rev']);
	} else {
		$edit->addArgUrl($oldUrl);
	}
	$edit->addArg($workingCopyFilePath);
	$edit->exec('Copy from '.getTarget().' to new destination '.$_POST['newname']);
	// set the properties in local copy
	foreach ($props as $name => $value) {
		$propset = new SvnEdit('propset');
		$propset->addArgOption($name, $value);
		$propset->addArgPath($workingCopyFilePath);
		$propset->exec("Set property '$name' to '$value'");
	}
	// commit
	$commit = new SvnEdit('commit');
	$commit->addArgPath($workingCopy);
	$commit->addArgRevpropsFromPost();
	if (isset($_POST['message'])) {
		$commit->setMessage($_POST['message']);
	}
	$commit->exec();
	// clean up
	System::deleteFolder($workingCopy);
	// done
	displayEdit($template, getParent($oldUrl), $newTarget);
}

/**
 * Duplicated from ../upload/index.php so if more operations need prop support we should extrac to reusable location.
 * @return {array(String)} arbitrary propery name to values
 */
function getCustomProperties() {
	$props = array();
	foreach ($_POST as $name => $value) {
		if (strBegins($name, UPLOAD_PROP_PREFIX)) {
			$props[substr($name, 5)] = $value;
		}
	}
	return $props;
}
?>
