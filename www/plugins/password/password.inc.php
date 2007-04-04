<?php
/**
 *
 *
 * @package
 */
if (!function_exists('getTarget')) require('../../account/login.inc.php');

// for inclusion with addPlugin
function password_getHeadTags() {
	return array();
}

// identify password files
if (strEnds(getTarget(), 'repos.user')) {
	header('Cache-Control: no-cache');
	$url = asLink(getWebapp().'account/password/?target='.urlencode(getTarget()));
	if (strpos(getSelfUrl(),'/edit/') === false) $url .= '&view=1';
	header('Location: '.$url);
}

?>
