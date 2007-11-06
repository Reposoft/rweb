<?php
/**
 * Presents index_xy.html
 */

require( 'conf/repos.properties.php' );
require( 'conf/Presentation.class.php' );

$p = Presentation::getInstance();
$p->assign('repository', getRepository());
$p->assign('host', getHost());

$p->display();

?>
