<?php
// Load an URL from a different domain.
// Needed to get AJAX resources with a global URL
require('../../open/ServiceRequest.class.php');

$acceptedUrls = array(
	'/^[a-z][a-z\/]+$/', // relative webapp (equal to same host)
	'/^'.preg_quote(getSelfRoot(),'/').'/', // same host
	'/^https?:\/\/www.repos.se\/.*(news|public).*/');

function isAccepted($url) {
	global $acceptedUrls;
	foreach ($acceptedUrls as $host) {
		if (preg_match($host, $url)) return true;
	}
	return false;
}

$url = null;

if (isset($_GET['url'])) $url = $_GET['url'];

if (isset($_GET['s'])) {
	$s = $_GET['s'];
	if (!defined('SERVICE_PUBLIC_'.$s)) trigger_error('Unknown service '.$s, E_USER_ERROR);
	// special case for client information, can't be proxied
	if ($s == SERVICE_PUBLIC_CLIENT) serviceClientNoProxy(); 
	// proxy to service page that might be protected on IP
	$url = constant('SERVICE_PUBLIC_'.$s);	
}

if (!$url) trigger_error('No service specified', E_USER_ERROR);

if (!isAccepted($url)) {
	trigger_error("The URL '$url' is not accepted", E_USER_ERROR);
}

$service = new ServiceRequest($url, array(), false);
$service->exec();

$h = $service->getResponseHeaders();
$status = array_shift($h);
header($status);
$forward = array(
	'Date', 'Server', 'Last-Modified', 'ETag',
	'Accept-Ranges', 'Content-Length', 'Content-Type');
foreach ($forward as $f) {
	if (isset($h[$f])) header($f.': '.$h[$f]);
}
echo $service->getResponse();

/**
 * Run the client information logic without an internal request
 */
function serviceClientNoProxy() {
	require('../../'.SERVICE_PUBLIC_CLIENT.'index.php');
	exit;
}

?>
