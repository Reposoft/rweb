<?php
/**
 * Reloads the apache configuration, if possible.
 * @package test
 */

require(dirname(dirname(__FILE__)).'/setup.inc.php');

setup_reloadApacheIfPossible();

$report->info('<a href="/?login">Repos login</a>');

$report->display();

?>
