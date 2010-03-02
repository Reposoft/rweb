<?php
/**
 * Allows administrators to help users with their logins.
 *
 * @package admin
 */

require( '../reposweb.inc.php' );
require( ReposWeb.'conf/Presentation.class.php' );

require('../admin-authorize.inc.php'); // login to view this section

$p = Presentation::getInstance();
$p->display();

?>
