<?php
/**
 *
 *
 * @package
 */

require('../../conf/Presentation.class.php');

$p = new Presentation();

if (function_exists('zend_loader_file_licensed')) {
	$p->assign('license', zend_loader_file_licensed());
}

$p->display();

?>
