<?php
/**
 *
 *
 * @package
 */

require('../../conf/Presentation.class.php');

// license entries that should not be printed
$skip = array('No-Lease-Error');

$p = new Presentation();

if (function_exists('zend_loader_file_licensed')) {
	$license = array_diff_assoc(zend_loader_file_licensed(), $skip);
	$p->assign('license', $license);
}

$p->display();

?>
