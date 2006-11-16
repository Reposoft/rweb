<?php
/**
 * Show a nice error message for page not found
 */
require('../../../conf/Presentation.class.php');
require('../../../account/login.inc.php');

$p = new Presentation();

$url = repos_getSelfUrl();

/*
echo('<--');
print_r($_REQUEST);
echo('-->');
 */

$p->showError('
HTTP 400 error. Invalid request from client.
');
?>