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

$p->showError('
Invalid request from browser. We would be interested to see how this could occur,
so feel free to contact <a href="mailto:support@repos.se">support@repos.se</a>.
', 'Bad Request');
?>