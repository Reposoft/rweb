<?php
/**
 *
 *
 * @package
 */
require('../../conf/Presentation.class.php');

$p = Presentation::getInstance();
$p->assign('commandbarUrl', getWebapp().'plugins/calendar/commandbar/');
$p->assign('calendarUrl', getWebapp().'lib/phpicalendar/phpicalendar/');
$p->display();

?>
