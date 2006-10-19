<?php
// get the details of a file and return as JSON or HTML
require( dirname(dirname(dirname(__FILE__)))."/account/login.inc.php" );

// what's the format?
// make same json for directory listing and single file details?
// how to use as plugin?

$cmd = 'list --xml '.escapeArgument(getTargetUrl()).'';
$return = login_svnPassthru($cmd);
if ($return) {
	login_handleSvnError($cmd, $return);
}

?>