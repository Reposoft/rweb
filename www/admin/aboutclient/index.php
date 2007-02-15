<?php
/**
 *
 *
 * @package
 */
require('../../conf/Presentation.class.php'); 

$p = new Presentation();
$p->assign('address', $_SERVER['REMOTE_ADDR']); 
$p->assign('local', $_SERVER['IS_LOCAL_CLIENT'] ? true : false);
$p->assign('admin', $_SERVER['IS_ADMIN_CLIENT'] ? true : false);
$p->display();

?>
