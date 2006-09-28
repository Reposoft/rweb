<?php
require('../../conf/repos.properties.php');
// uses a shell script, but there is now a php version too
if (isWindows()) {
	header("Location: /repos/test/setup/setup.php");
	exit;
}
// the shell script will be removed when we know that the php version works on all platforms
header('Content-type: text/plain; charset=utf-8');
$cmd = dirname(__FILE__).'/setup.sh 2>&1';
$result = passthru($cmd);
if ($result) {
	echo("\n\nThe test setup script exited with error code");
} else {
	echo("\n\nTest setup script complete");
}

?>