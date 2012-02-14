<?php
/**
 * This is the pre-login page for anyone.
 */

require( 'conf/repos.properties.php' );
require( 'conf/Presentation.class.php' );

$p = Presentation::getInstance();

// check authentication status
$auth = array('http'=>false, 'repos'=>false);
if (isset($_SERVER['PHP_AUTH_USER'])) $auth['http'] = $_SERVER['PHP_AUTH_USER'];
if (isset($_COOKIE[USERNAME_KEY])) $auth['repos'] = $_COOKIE[USERNAME_KEY]; 
// TODO we definitely need a login challenge to have a chance to detect http auth

/*
$webapp = getWebappUrl();
$referrer = getHttpReferer();
$selfUrl = getSelfUrl();
$repoRoot = getRepositoryRoot();
print_r(array('webapp'=>$webapp, 'ref'=>$referrer, 'self'=>$selfUrl, 'reporoot'=>$repoRoot, 'auth'=>$auth)); exit;
*/

$p->assign('auth',$auth);

// display public configuration values
$p->assign('host', getHost());

$p->display();

?>
