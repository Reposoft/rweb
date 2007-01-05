<?php
/**
 * Results URL for selenium test runner.
 * 
 * @package test
 */
header("Content-type: text/plain");

$name = date('Y-m-d_His');
$logpath = dirname(dirname(dirname(dirname(__FILE__)))).'/testresults/';
$logfile = $logpath.$name;

// client information
$client = array(
'browser' => $_SERVER["HTTP_USER_AGENT"],
'address' => $_SERVER["REMOTE_ADDR"],
'local' => isset($_SERVER["IS_LOCAL_CLIENT"]), // defined by repos server config
'admin' => isset($_SERVER["IS_ADMIN_CLIENT"]) // defined by repos server config
);

if (isset($_SERVER["REMOTE_USER"])) {
	$client['remoteuser'] = $_SERVER["REMOTE_USER"];
} else {
	$client['remoteuser'] = '';
}
if (isset($_SERVER["PHP_AUTH_USER"])) {
	$client['reposuser'] = $_SERVER["PHP_AUTH_USER"];
} else {
	$client['reposuser'] = '';
}

// repos server variables
$server = array(
'hostname' => $_SERVER["HTTP_HOST"],
'address' => $_SERVER["SERVER_ADDR"],
'https' => isset($_SERVER["HTTPS"]),
'phpversion' => phpversion()
);

$result = '';
foreach($server as $key => $value) {
	$result .= '[server:'.$key.']: '.$value."\n";
}
foreach($client as $key => $value) {
	$result .= '[client:'.$key.']: '.$value."\n";
}
foreach($_REQUEST as $key => $value) {
	$result .= '['.$key.']: '.$value."\n";
}

if (touch($logfile)) {
	$h = fopen($logfile,'w');
	fwrite($h, $result);
	fclose($h);
} else {
	$email = $_SERVER["SERVER_ADMIN"];
	if (strpos($email, '@')===false) $email = 'admin@repos.se';
	mail($email,
		"Test results $name",
		"You get these mails because test results could not be written to $logfile\n\n".
		$result);
}

echo($result);

?>
