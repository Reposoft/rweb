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
$serverName = $_SERVER['SERVER_NAME'];
$httpHost = $_SERVER['HTTP_HOST'];
if (true || /* can we trust SERVER_NAME to be a configured one? */$serverName != $httpHost) {
	$enforceHostMessage = 'Could be that the address used for the connection is unexpected, for example an IP-number or a name without a domain.';
}

$p->showErrorNoRedirect('The requested resource is not available. ' . $enforceHostMessage,
		'Gone');
?>