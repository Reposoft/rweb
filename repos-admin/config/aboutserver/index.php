<?php
/**
 *
 *
 * @package
 */
require( '../../reposweb.inc.php' );
require( ReposWeb.'conf/Presentation.class.php' );

$p = new Presentation();
$p->assign('primary', $_SERVER['REPOS_PRIMARY'] ? true : false);
$p->display();

?>
