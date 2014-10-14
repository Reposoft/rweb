<?php
/**
 * 500 Internal Server Error
 */
define('REPOS_SERVICE_NAME', 'errors/410/');
require('../../conf/Presentation.class.php');

$p = Presentation::getInstance();

$url = getSelfUrl();

// help CMS users detect a common cause of this error
$enforceHostMessage = '';
$configHost = getHost();
$httpHost = $_SERVER['HTTP_HOST'];
if (true) {
	$enforceHostMessage = 'Could be that the address used for the connection is unexpected, for example an IP-number or a name without a domain.';
}
// this host information is already available in page meta tag repos-repository so we can expose it more clearly
if ($configHost && !strEnds($configHost, $httpHost)) {
	$root = asLink($configHost);
	$enforceHostMessage .= " Try the configured name <a href=\"$url/?rweb=start\">$root</a>";
}

$p->showErrorNoRedirect('The requested resource is not available. ' . $enforceHostMessage,
		'Gone');
?>