<?php
/**
 *
 *
 * @package
 */
require( '../../admin.inc.php' );
require( ReposWeb.'conf/Presentation.class.php' );

$p = new Presentation();
$p->assign('primary', $_SERVER['REPOS_PRIMARY'] ? true : false);
$p->assign('email', getAdministratorEmail());
// don't display the local paths
$p->assign('userfile', getAdminUserFile() ? true : false);
$p->assign('accessfile', getAdminAccessFile() ? true : false);
$p->display();

?>
