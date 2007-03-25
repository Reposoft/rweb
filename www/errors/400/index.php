<?php
/**
 * 400 Bad Request
 */
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
so please contact <a href="mailto:support@repos.se">support@repos.se</a>.
', 'Bad Request');
?>