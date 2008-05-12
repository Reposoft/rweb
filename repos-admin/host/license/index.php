<?php
/**
 *
 *
 * @package
 */

require( '../../reposweb.inc.php' );
require( ReposWeb.'conf/Presentation.class.php' );

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
	$zend = zend_loader_file_licensed();
	if (is_array($zend)) {
		$license = array_diff_key(zend_loader_file_licensed(), $skip);
		$p->assign('license', $license);
	} else {
		$p->assign('license', false);
	}
}

$p->display();

?>
