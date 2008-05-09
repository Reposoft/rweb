<?php
/**
 * Allows administrators to help users with their logins.
 *
 * @package admin
 */

require( '../reposweb.inc.php' );
require( ReposWeb.'conf/Presentation.class.php' );

$p = Presentation::getInstance();
$p->display();

?>
