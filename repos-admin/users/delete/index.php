<?php
/**
 *
 *
 * @package
 */
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');

$p = new Presentation();
$p->display();

?>
