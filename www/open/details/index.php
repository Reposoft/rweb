<?php
// get the details of a file and return as JSON or HTML
require( dirname(dirname(dirname(__FILE__)))."/account/login.inc.php" );

$cmd = 'list --xml '.escapeArgument(getTargetUrl()).'';
$return = login_svnPassthru($cmd);
if ($return) {
	login_handleSvnError($cmd, $return);
}

?>