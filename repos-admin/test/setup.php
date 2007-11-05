<?php
/**
 * Sets up a simpletest base class for repos-admin PHP unit tests.
 *
 * @package admin
 */
 
// get the reposweb setting
require_once( dirname(dirname(__FILE__)).'/reposweb.inc.php' );
// get the test framework
require( ReposWeb.'/lib/simpletest/setup.php' );

?>
