<?php
/**
 *
 *
 * @package
 */
if (!function_exists('getTarget')) require('../../account/login.inc.php');

// for inclusion with addPlugin
function calendar_getHeadTags() {
	return array();
}

// iCalendar files
if (strEnds(getTarget(), '.ics')) {
	setcookie("repos-calendar", getTargetUrl(), time()+3600, '/');
	header("Cache-Control: no-cache");
	header("Location: ".asLink(getWebapp()."plugins/calendar/"));
	exit;
}
 

?>
