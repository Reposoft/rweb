<?php
/**
 *
 *
 * @package
 */
if (!function_exists('getTarget')) require('../../account/login.inc.php');

// for inclusion with addPlugin
function calendar_getHeadTags() {
	return '';
}

// iCalendar files
if (strEnds(getTarget(), '.ics')) {
	setcookie("repos-calendar", getTargetUrl(), time()+3600, '/');
	header("Cache-Control: no-cache");
	header("Location: ".getWebapp()."plugins/calendar/");
	exit;
}
 

?>
