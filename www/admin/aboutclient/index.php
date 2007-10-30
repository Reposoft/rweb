<?php
/**
 *
 *
 * @package
 */
require('../../conf/Presentation.class.php'); 

/**
 * Client address is not always same as REMOTE_ADDRESS,
 * for example if there is a local SSL proxy
 */
function getClientAddress() {
	if (isset($_SERVER['HTTP_X_FORWARDED_SERVER'])) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			if ($_SERVER['HTTP_X_FORWARDED_SERVER']='127.0.0.1') {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
	}
	return $_SERVER['REMOTE_ADDR'];
}

$p = new Presentation();
$p->assign('address', getClientAddress()); 
$p->assign('local', $_SERVER['IS_LOCAL_CLIENT'] ? true : false);
$p->assign('admin', $_SERVER['IS_ADMIN_CLIENT'] ? true : false);
$p->display();

?>
