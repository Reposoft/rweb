<?php
/**
 * 500 Internal Server Error
 */
define('REPOS_SERVICE_NAME', 'errors/500/');
require('../../conf/Presentation.class.php');

$p = Presentation::getInstance();

$url = getSelfUrl();

$p->showErrorNoRedirect('
This is a server error that could not be handled automatically, at the URL '.$url.'.
<br />This type of error is often temporary. If it happens repeatedly, 
contact your administrator.
', 'Internal Server Error');
?>