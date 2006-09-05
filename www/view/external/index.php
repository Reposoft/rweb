<?php
// Load an URL from a different domain.
// Needed to get AJAX resources with a global URL

$acceptedHosts = array('/^https:\/\/www.repos.se\.*/');

function isAccepted($url) {
	global $acceptedHosts;
	foreach ($acceptedHosts as $host) {
		if (preg_match($host, $url)) return true;
	}
	return false;
}

function getContentType($url) {
	// currently we're only using this for XML files
	return "text/xml";
}

// open the file in a binary mode
$url = $_GET['url'];
if (!isAccepted($url)) {
	trigger_error("Error: The 'url' parameter value is not accepted: $url");
	exit;
}

$fp = fopen($url, 'r');

// send the right headers
header("Content-Type: ".getContentType($url));
//header("Content-Length: " . filesize($name));

// dump the picture and stop the script
fpassthru($fp);
fclose($fp);
exit;

?>
