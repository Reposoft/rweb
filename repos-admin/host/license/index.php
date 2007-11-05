<?php
/**
 *
 *
 * @package
 */

require('../../conf/Presentation.class.php');

// license entries that should not be printed
$skip = array(
	'No-Lease-Error'=>'',
	'Produced-By'=>'');

$p = new Presentation();

if (function_exists('zend_get_id')) {
	$id = zend_get_id();
	$p->assign('hostid', $id[0]);
}

if (function_exists('zend_loader_file_licensed')) {
	$license = array_diff_key(zend_loader_file_licensed(), $skip);
	$p->assign('license', $license);
}

$p->display();

?>
