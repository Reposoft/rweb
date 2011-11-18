<?php
/**
 * 400 Bad Request
 */
define('REPOS_SERVICE_NAME', 'errors/400/');
require('../../conf/Presentation.class.php');

$p = Presentation::getInstance();

$url = getSelfUrl();

/*
echo('<--');
print_r($_REQUEST);
echo('-->');
 */

$p->showErrorNoRedirect('
The server can not process the request. We would be interested to see how this could occur,
so please contact your administrator.
', 'Bad Request');
?>