<?php
/**
 *
 *
 * @package
 */
if (!function_exists('getTarget')) require('../../account/login.inc.php');

// for inclusion with addPlugin
function password_getHeadTags() {
	return '';
}

// identify password files
if (strEnds(getTarget(), 'repos-password.htp')) {
	header('Cache-Control: no-cache');
	$url = getWebapp().'account/password/?target='.urlencode(getTarget());
	if (strpos(getSelfUrl(),'/edit/') === false) $url .= '&view=1';
	header('Location: '.$url);
}

?>
