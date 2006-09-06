<?php
header('Content-type: text/plain; charset=utf-8');

$cmd = dirname(__FILE__).'/setup.sh 2>&1';
$result = passthru($cmd);
if ($result) {
	echo("\n\nThe test setup script exited with error code");
} else {
	echo("\n\nTest setup script complete");
}

?>