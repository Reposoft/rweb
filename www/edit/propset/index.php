<?php
/**
 * Set svn property. Currently only used in service requests.
 *
 * @package
 */
require('../../conf/Presentation.class.php');
require('../SvnEdit.class.php');

Validation::expect('target','name','value');
svnPropset(getTarget,
	$_REQUEST['name'], $_REQUEST['value']);

function svnPropset($target, $name, $value) {
	$presentation = Presentation::getInstance();
	$workingCopy = System::getTempFolder('propset');
	$targetFolder = getParent(getTargetUrl());
	$filename = getPathName(getTargetUrl());
	// propset can only be done in working copy
	$checkout = new SvnEdit('checkout');
	$checkout->addArgOption('--non-recursive');
	$checkout->addArgUrl($targetFolder);
	$checkout->addArgPath($workingCopy);
	$checkout->exec('Check out latest version');
	// set the property in local copy
	$propset = new SvnEdit('propset');
	$propset->addArgOption($name, $value);
	$propset->addArgPath($workingCopy.$filename);
	$propset->exec("Set property '$name' to '$value'");
	// commit
	$commit = new SvnEdit('commit');
	$commit->addArgPath($workingCopy);
	$commit->addArgOption('-m',"Set property '$name' to '$value'");
	$commit->exec();
	// clean up
	System::deleteFolder($workingCopy);
	// done
	displayEdit($presentation);
} 

?>
