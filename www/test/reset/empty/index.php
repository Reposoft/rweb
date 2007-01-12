<?php
/**
 *
 *
 * @package
 */
 
require(dirname(dirname(__FILE__)).'/setup.inc.php');

setup_deleteCurrent();

$report->ok('Created empty repository folder '.$repo);

$report->info('You may wish to proceed to <a id="create" href="/repos/admin/create/">create repository</a>.');

$report->display();

?>
