<?php
/**
 * Presents index_xy.html
 */

require( 'conf/repos.properties.php' );
require( 'conf/Presentation.class.php' );

$p = Presentation::getInstance();

// check authentication status
$auth = array('http'=>false, 'repos'=>false);
if (isset($_SERVER['PHP_AUTH_USER'])) $auth['http'] = $_SERVER['PHP_AUTH_USER'];
if (isset($_COOKIE[USERNAME_KEY])) $auth['repos'] = $_COOKIE[USERNAME_KEY]; 
// TODO we definitely need a login challenge to have a chance to detect http auth

$p->assign('auth',$auth);

// display public configuration values
$p->assign('repository', getRepository());
$p->assign('host', getHost());

$p->display();

?>
