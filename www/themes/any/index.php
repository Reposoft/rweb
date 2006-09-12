<?php
require(dirname(dirname(dirname(__FILE__))).'/account/login.inc.php');

if (!isset($_GET['u'])) {
	trigger_error("Default url, the 'u' parameter, must be set");
	exit;
}
$u = $_GET['u'];
// find out if it is a nocache url
$nocache = '';
foreach ($_GET as $key => $value) {
	if (is_numeric($key)) {
		$nocache = $nocache.'?'.$key;
		break;
	}
}

if (!isset($_COOKIE[USERNAME_KEY])) {
	$theme = '';
} else {
	$theme = repos_getUserTheme($_COOKIE[USERNAME_KEY]);
}

if (substr($u, 0, 11)=='settings.js') {
	header('Location: /repos/'.$theme.$u.$nocache);
	exit;
}

header('Location: /repos/'.$theme.'style/'.$u);